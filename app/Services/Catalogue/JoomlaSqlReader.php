<?php

namespace App\Services\Catalogue;

use RuntimeException;

/** Reads selected dump data as text. It never sends source SQL to a database. */
class JoomlaSqlReader
{
    private const TABLES = [
        'virtuemart_products', 'virtuemart_products_en_gb', 'virtuemart_categories',
        'virtuemart_categories_en_gb', 'virtuemart_category_categories', 'virtuemart_product_categories',
        'virtuemart_medias', 'virtuemart_product_medias', 'virtuemart_category_medias',
        'virtuemart_customs', 'virtuemart_product_customfields', 'menu', 'finder_links', 'redirect_links',
    ];

    /**
     * @return array{prefix: string, tables: array<string, list<array<string, string|null>>>, checksum: string}
     */
    public function read(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Cannot read the SQL dump.');
        }
        try {
            $prefix = null;
            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)? `([^`]*?)virtuemart_products`/', $line, $match)) {
                    $prefix = $match[1];
                    break;
                }
            }
            if ($prefix === null) {
                throw new RuntimeException('No VirtueMart products table was found in the dump.');
            }
            rewind($handle);
            $allowed = array_combine(array_map(fn ($name) => $prefix.$name, self::TABLES), self::TABLES);
            $schemas = [];
            $tables = array_fill_keys(self::TABLES, []);
            $creating = null;
            $statement = '';
            $inserting = null;
            $quote = null;
            $escaped = false;
            while (($line = fgets($handle)) !== false) {
                if ($inserting !== null) {
                    $statement .= $line;
                    if ($this->endsStatement($line, $quote, $escaped)) {
                        $this->insert($statement, $inserting, $schemas, $tables);
                        $statement = '';
                        $inserting = null;
                    }

                    continue;
                }
                if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)? `([^`]+)`/', $line, $match)) {
                    $creating = $allowed[$match[1]] ?? null;
                    if ($creating !== null) {
                        $schemas[$creating] = [];
                    }
                } elseif ($creating !== null) {
                    if (preg_match('/^\s*`([^`]+)`/', $line, $match)) {
                        $schemas[$creating][] = $match[1];
                    }
                    if (preg_match('/^\)\s*(?:ENGINE|;)/', $line)) {
                        $creating = null;
                    }
                }
                if (preg_match('/^INSERT INTO `([^`]+)`/', $line, $match) && isset($allowed[$match[1]])) {
                    $inserting = $allowed[$match[1]];
                    $statement = $line;
                    $quote = null;
                    $escaped = false;
                    if ($this->endsStatement($line, $quote, $escaped)) {
                        $this->insert($statement, $inserting, $schemas, $tables);
                        $statement = '';
                        $inserting = null;
                    }
                }
            }
            if ($inserting !== null) {
                throw new RuntimeException('The SQL dump has an incomplete catalogue INSERT.');
            }
            foreach (['virtuemart_products', 'virtuemart_products_en_gb', 'virtuemart_categories', 'virtuemart_categories_en_gb', 'virtuemart_medias'] as $required) {
                if (! isset($schemas[$required])) {
                    throw new RuntimeException('Missing required source table: '.$required);
                }
            }

            $checksum = hash_file('sha256', $path);
            if ($checksum === false) {
                throw new RuntimeException('Could not checksum the source dump.');
            }

            return ['prefix' => $prefix, 'tables' => $tables, 'checksum' => $checksum];
        } finally {
            fclose($handle);
        }
    }

    private function endsStatement(string $text, ?string &$quote, bool &$escaped): bool
    {
        for ($i = 0, $length = strlen($text); $i < $length; $i++) {
            $char = $text[$i];
            if ($escaped) {
                $escaped = false;
            } elseif ($quote !== null) {
                if ($char === '\\') {
                    $escaped = true;
                } elseif ($char === $quote) {
                    if (($text[$i + 1] ?? '') === $quote) {
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
            } elseif ($char === "'" || $char === '"') {
                $quote = $char;
            } elseif ($char === ';') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, list<string>>  $schemas
     * @param  array<string, list<array<string, string|null>>>  $tables
     */
    private function insert(string $sql, string $table, array $schemas, array &$tables): void
    {
        if (! preg_match('/\AINSERT INTO `[^`]+`(?:\s*\(([^)]*)\))?\s+VALUES\s*/s', $sql, $match)) {
            throw new RuntimeException('Unsupported catalogue INSERT format: '.$table);
        }
        $columns = empty($match[1]) ? ($schemas[$table] ?? []) : array_map(fn ($column) => trim($column, " `\t\r\n"), explode(',', $match[1]));
        $i = strlen($match[0]);
        $length = strlen($sql);
        while ($i < $length) {
            while ($i < $length && (ctype_space($sql[$i]) || $sql[$i] === ',')) {
                $i++;
            }
            if (($sql[$i] ?? ';') === ';') {
                break;
            }
            if (($sql[$i++] ?? '') !== '(') {
                throw new RuntimeException('Invalid catalogue row: '.$table);
            }
            $values = [];
            while (true) {
                while ($i < $length && ctype_space($sql[$i])) {
                    $i++;
                }
                $values[] = $this->value($sql, $i);
                while ($i < $length && ctype_space($sql[$i])) {
                    $i++;
                }
                $delimiter = $sql[$i++] ?? '';
                if ($delimiter === ')') {
                    break;
                }
                if ($delimiter !== ',') {
                    throw new RuntimeException('Invalid catalogue value delimiter: '.$table);
                }
            }
            if (count($values) !== count($columns)) {
                throw new RuntimeException('Catalogue column count does not match: '.$table);
            }
            $tables[$table][] = array_combine($columns, $values);
        }
    }

    private function value(string $sql, int &$i): ?string
    {
        if (! isset($sql[$i])) {
            throw new RuntimeException('Missing catalogue value.');
        }
        $quote = $sql[$i];
        if ($quote === "'" || $quote === '"') {
            $i++;
            $value = '';
            while ($i < strlen($sql)) {
                $char = $sql[$i++];
                if ($char === '\\') {
                    if ($i >= strlen($sql)) {
                        throw new RuntimeException('Unterminated catalogue escape sequence.');
                    }
                    $escaped = $sql[$i];
                    $i++;
                    $value .= match ($escaped) {
                        'n' => "\n", 'r' => "\r", 't' => "\t", '0' => "\0", 'Z' => "\x1a",
                        '%', '_' => '\\'.$escaped,
                        default => $escaped,
                    };
                } elseif ($char === $quote) {
                    if (($sql[$i] ?? '') === $quote) {
                        $value .= $quote;
                        $i++;
                    } else {
                        return $value;
                    }
                } else {
                    $value .= $char;
                }
            }
            throw new RuntimeException('Unterminated catalogue string.');
        }
        $start = $i;
        while (isset($sql[$i]) && $sql[$i] !== ',' && $sql[$i] !== ')') {
            $i++;
        }
        $value = trim(substr($sql, $start, $i - $start));
        if (strtoupper($value) === 'NULL') {
            return null;
        }
        if (preg_match('/^0x([a-f0-9]+)$/i', $value, $match)) {
            return hex2bin($match[1]) ?: '';
        }
        if (! is_numeric($value)) {
            throw new RuntimeException('Unsupported literal in catalogue dump.');
        }

        return $value;
    }
}
