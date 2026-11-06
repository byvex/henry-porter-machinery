<?php

return [
    'brands' => [
        'bobman' => [
            'description' => '<p>Jydeland Maskinfabrik A/S is a family-owned Danish manufacturer with over a century of engineering heritage, supplying labour-saving Bobman cubicle cleaning, bedding, and feeding machines to dairy and livestock farms worldwide. Featuring durable galvanised steel construction alongside proven diesel and quiet lithium-ion electric power options, the Bobman range sweeps stalls, scrapes slats, and spreads bedding or disinfectant in a single pass.</p>',
        ],
        'multione' => [
            'description' => '<p>MultiOne articulated compact loaders combine Italian engineering, all-wheel hydrostatic traction, and telescopic boom reach with a choice of over 170 attachments. From the ultra-compact 3WD 1 Series and high-torque Stage V diesel models through to the full-electric EZ Emission Zero range, MultiOne loaders transform daily handling tasks across farming, estates, landscaping, and construction.</p>',
        ],
    ],

    'retired_products' => [
        [
            'slug' => 'bobman-s',
            'brand_slug' => 'bobman',
        ],
        [
            'slug' => 'multione-compact-loaders',
            'brand_slug' => 'multione',
        ],
    ],

    'products' => [
        // =========================================================================
        // BOBMAN RANGE (5 Published Machines)
        // =========================================================================
        [
            'slug' => 'bobman-super',
            'name' => 'Bobman Super',
            'brand_slug' => 'bobman',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 10,
            'categories' => ['cubicle-cleaners'],
            'summary' => '<p>Compact, 99% galvanised 3-in-1 cubicle cleaner and bedding spreader for small to medium dairy herds, available in both quiet Lithium-ion Electric and 10 HP Hatz Diesel models.</p>',
            'description_html' => <<<'HTML'
<h2>3-in-1 Galvanised Cubicle Cleaning &amp; Bedding Spreader</h2>
<p>For farmers who need a compact, corrosion-resistant, and versatile bedding machine, the <strong>Bobman Super</strong> is the proven choice. Built with a durable <strong>galvanised steel</strong> hopper and chassis designed to withstand harsh barn environments, the Bobman Super scrapes the slats, sweeps the stalls, and spreads an even layer of bedding all in a single pass.</p>
<p>Its compact dimensions and tight 1.6 m turning diameter allow the machine to operate effortlessly in narrow passages, robotic milking barns, and cubicles, and to be stored directly inside the shed to prevent cross-contamination. It can be loaded manually (ideal for bagged sawdust or lime) or filled quickly with a compact loader.</p>

<h3>Electric &amp; Diesel Power Options</h3>
<ul>
    <li><strong>Bobman Super Electric (New):</strong> Powered by a quiet (61 dB), zero-emission 4 kW electric motor and high-performance LiFePO4 Lithium-ion battery (52.2V / 60 Ah / 6.1 kWh). With no engine oil or fuel filters to service and an onboard charger that recharges in just 4 hours, it delivers up to 90 minutes of operation per charge without exhaust fumes or livestock disturbance.</li>
    <li><strong>Bobman Super Diesel:</strong> Powered by a tried-and-tested 10 HP Hatz 1-cylinder air-cooled diesel engine with electric start and a 5 L fuel tank, providing up to 180 minutes of continuous runtime for dependable daily work.</li>
</ul>

<h3>Key Benefits &amp; Features</h3>
<ul>
    <li><strong>3-in-1 Single-Pass Operation:</strong> Scrapes passageways, brushes stall beds, and spreads bedding simultaneously to cut daily cubicle work to minutes.</li>
    <li><strong>Galvanised Construction:</strong> Silver hot-dip galvanised finish resists corrosion from slurry, lime, and moisture for long-term durability.</li>
    <li><strong>Improved Herd Health:</strong> Regular cleaning and dry, even bedding help reduce somatic cell counts, prevent mastitis, and support cow comfort and milk yield.</li>
    <li><strong>Versatile Bedding Materials:</strong> Spreads chopped straw (up to 5–6 cm), sawdust, wood shavings, paper granules, lime, or disinfectant powder with adjustable dosing.</li>
    <li><strong>Hydraulic Operation:</strong> Hydraulically driven brush and spreader mechanism with 3-wheel drive (3WD) traction.</li>
</ul>

<h3>Optional Tools &amp; Attachments</h3>
<ul>
    <li><strong>Disinfection Spreader Box (10 L / 15 kg):</strong> Applies lime or disinfectant powder directly onto the stall beds during the same pass.</li>
    <li><strong>Top Box Extensions:</strong> Increases hopper capacity from 500 L up to 700 L (or 900 L with double top box).</li>
    <li><strong>Extra Mixer:</strong> Ensures consistent flow when spreading chopped straw.</li>
    <li><strong>Parallelogram / Floating Arm:</strong> Adjusts brush height automatically across stalls with varying kerb or bedding heights.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>Bobman Super Electric</th>
            <th>Bobman Super Diesel</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Power / Engine</strong></td>
            <td>4 kW Electric Motor</td>
            <td>Hatz 10 HP (1-cylinder air-cooled)</td>
        </tr>
        <tr>
            <td><strong>Energy Source</strong></td>
            <td>LiFePO4 Lithium Battery (52.2V – 60 Ah / 6.1 kWh)</td>
            <td>Diesel (5 L fuel tank)</td>
        </tr>
        <tr>
            <td><strong>Operating Time</strong></td>
            <td>Up to 90 min (4 hr built-in charger)</td>
            <td>Up to 180 min</td>
        </tr>
        <tr>
            <td><strong>Drive Speed</strong></td>
            <td>Up to 10 km/h</td>
            <td>Up to 8 km/h</td>
        </tr>
        <tr>
            <td><strong>Noise Level</strong></td>
            <td>61 dB</td>
            <td>96 dB</td>
        </tr>
        <tr>
            <td><strong>Wheel Drive</strong></td>
            <td>3WD</td>
            <td>3WD (1WD optional)</td>
        </tr>
        <tr>
            <td><strong>Loading Method</strong></td>
            <td colspan="2">Manual / External Loader</td>
        </tr>
        <tr>
            <td><strong>Hopper Capacity</strong></td>
            <td colspan="2">500 L – 700 L (with top box)</td>
        </tr>
        <tr>
            <td><strong>Spreading Distance</strong></td>
            <td colspan="2">50 cm – 200 cm (up to 3 m)</td>
        </tr>
        <tr>
            <td><strong>Bedding Materials</strong></td>
            <td colspan="2">Chopped straw, sawdust, wood shavings, paper granules, lime, disinfectant powder</td>
        </tr>
        <tr>
            <td><strong>Brush Diameter</strong></td>
            <td colspan="2">75 – 85 cm</td>
        </tr>
        <tr>
            <td><strong>Scraper Width</strong></td>
            <td colspan="2">1.0 m</td>
        </tr>
        <tr>
            <td><strong>Dimensions (L × W × H)</strong></td>
            <td colspan="2">2.4 m × 1.0 m × 1.3 m</td>
        </tr>
        <tr>
            <td><strong>Turning Diameter / Ground Clearance</strong></td>
            <td colspan="2">1.6 m / 20 cm</td>
        </tr>
        <tr>
            <td><strong>Weight</strong></td>
            <td colspan="2">Approx. 435 – 500 kg</td>
        </tr>
    </tbody>
</table>
HTML,
            'videos' => ['iFnVe5SQfX4', 'YeU-ikT1Qxo'],
            'preserve_existing_testimonials' => true,
            'gallery' => [
                [
                    'existing_source_path' => 'images/virtuemart/product/Bobman Super Galv.jpg',
                    'alt_text' => 'Bobman Super galvanised cubicle cleaner and bedding spreader in dairy barn',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-061-scaled.jpg',
                    'filename' => 'bobman-super-galvanised-side.jpg',
                    'alt_text' => 'Bobman Super galvanised model with rotary brush and disinfection box',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-210-scaled.jpg',
                    'filename' => 'bobman-super-galvanised-brushing.jpg',
                    'alt_text' => 'Galvanised Bobman Super sweeping cubicles in one pass',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-104-scaled.jpg',
                    'filename' => 'bobman-super-galvanised-loading.jpg',
                    'alt_text' => 'Loading sawdust into the galvanised Bobman Super hopper',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-234-scaled.jpg',
                    'filename' => 'bobman-super-galvanised-passage.jpg',
                    'alt_text' => 'Galvanised Bobman Super operating along cubicle passageway',
                ],
            ],
        ],

        [
            'slug' => 'bobman-fl',
            'name' => 'Bobman Front Load (FL)',
            'brand_slug' => 'bobman',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 20,
            'categories' => ['cubicle-cleaners'],
            'summary' => '<p>Self-loading, galvanised 3-in-1 cubicle cleaner and bedding spreader that tips forward to scoop directly from a sawdust or chopped straw stack—available in Electric and Diesel models.</p>',
            'description_html' => <<<'HTML'
<h2>Self-Loading Galvanised Bedding Spreader &amp; Cubicle Cleaner</h2>
<p>The <strong>Bobman Front Load (FL)</strong> combines the compact manoeuvrability of the Super with a hydraulic <strong>self-loading front hopper</strong>, allowing one operator to load directly from a sawdust or chopped straw bay without needing a separate loader or tractor. Featuring a heavy-duty <strong>galvanised steel</strong> hopper and chassis, the Bobman FL scrapes slats, sweeps stall beds, and spreads a uniform layer of bedding in a single pass.</p>
<p>Designed to be stored and operated inside the cubicle house—including sheds with robotic milking systems—the Front Load saves substantial daily labour while economising on bedding material and maintaining clean, dry cow resting surfaces.</p>

<h3>Electric &amp; Diesel Power Options</h3>
<ul>
    <li><strong>Bobman Front Load Electric (New):</strong> Equipped with a 4.5 kW electric drive system and a 52.2V / 60 Ah (6.1 kWh) LiFePO4 Lithium-ion battery. Operating at just 61 dB with zero exhaust emissions, it provides up to 60 minutes of self-loading and spreading work per charge, with a built-in charger for convenient barn charging.</li>
    <li><strong>Bobman Front Load Diesel:</strong> Powered by a reliable 10 HP Hatz 1-cylinder air-cooled diesel engine with electric start and a 5 L fuel tank, delivering up to 180 minutes of runtime per tank.</li>
</ul>

<h3>Key Benefits &amp; Features</h3>
<ul>
    <li><strong>Hydraulic Self-Loading Hopper:</strong> Tips forward to scoop bedding directly from a pile, eliminating manual shovelling or secondary loading machinery.</li>
    <li><strong>Galvanised Finish:</strong> Hot-dip galvanised steel hopper and frame provide superior protection against corrosion in livestock housing.</li>
    <li><strong>3-in-1 Single-Pass Workflow:</strong> Simultaneously scrapes the passageway, brushes the cubicles, and spreads bedding (and disinfectant when fitted with the optional box).</li>
    <li><strong>Herd Hygiene &amp; Milk Quality:</strong> Keeps cubicles clean and dry to help lower somatic cell counts and reduce the risk of mastitis.</li>
    <li><strong>Tight Turning Circle:</strong> All-wheel 3WD traction with a 1.6 m turning diameter for effortless handling in narrow passages.</li>
</ul>

<h3>Optional Tools &amp; Attachments</h3>
<ul>
    <li><strong>Disinfection Spreader (10 L / 15 kg):</strong> Applies lime or disinfectant powder evenly while brushing and bedding.</li>
    <li><strong>Top Box Extension (100–150 L):</strong> Expands hopper volume from 600 L up to 750 L.</li>
    <li><strong>Extra Mixer:</strong> Assists with agitating and spreading chopped straw (up to 5–6 cm).</li>
    <li><strong>Floating Arm / Parallelogram:</strong> Follows uneven cubicle kerbs automatically while sweeping.</li>
    <li><strong>Weight Block (33 kg):</strong> Adds rear counterweight when self-loading heavier bedding materials.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>Bobman Front Load Electric</th>
            <th>Bobman Front Load Diesel</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Power / Engine</strong></td>
            <td>4.5 kW Electric Motor</td>
            <td>Hatz 10 HP (1-cylinder air-cooled)</td>
        </tr>
        <tr>
            <td><strong>Energy Source</strong></td>
            <td>LiFePO4 Lithium Battery (52.2V – 60 Ah / 6.1 kWh)</td>
            <td>Diesel (5 L fuel tank)</td>
        </tr>
        <tr>
            <td><strong>Operating Time</strong></td>
            <td>Up to 60 min (built-in charger)</td>
            <td>Up to 180 min</td>
        </tr>
        <tr>
            <td><strong>Drive Speed</strong></td>
            <td>Up to 10 km/h</td>
            <td>Up to 8 km/h</td>
        </tr>
        <tr>
            <td><strong>Noise Level</strong></td>
            <td>61 dB</td>
            <td>96 dB</td>
        </tr>
        <tr>
            <td><strong>Wheel Drive</strong></td>
            <td>3WD</td>
            <td>3WD</td>
        </tr>
        <tr>
            <td><strong>Loading Method</strong></td>
            <td colspan="2">Hydraulic Self-Loading (Front Tip)</td>
        </tr>
        <tr>
            <td><strong>Hopper Capacity</strong></td>
            <td colspan="2">500 – 600 L (up to 750 L with top box)</td>
        </tr>
        <tr>
            <td><strong>Spreading Distance</strong></td>
            <td colspan="2">50 cm – 200 cm (up to 3 m)</td>
        </tr>
        <tr>
            <td><strong>Bedding Materials</strong></td>
            <td colspan="2">Chopped straw (max 5–6 cm), sawdust, wood chips/shavings, paper granules, lime, disinfectant</td>
        </tr>
        <tr>
            <td><strong>Brush Diameter</strong></td>
            <td colspan="2">75 – 85 cm</td>
        </tr>
        <tr>
            <td><strong>Scraper Width</strong></td>
            <td colspan="2">1.0 m</td>
        </tr>
        <tr>
            <td><strong>Dimensions (L × W × H)</strong></td>
            <td colspan="2">2.3–2.4 m × 1.0 m × 1.3 m</td>
        </tr>
        <tr>
            <td><strong>Turning Diameter / Ground Clearance</strong></td>
            <td colspan="2">1.6 m / 20 cm</td>
        </tr>
        <tr>
            <td><strong>Weight</strong></td>
            <td colspan="2">550 kg</td>
        </tr>
    </tbody>
</table>
HTML,
            'videos' => ['j93QpNT8wVU', 'YeU-ikT1Qxo'],
            'preserve_existing_testimonials' => true,
            'gallery' => [
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-140-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-side.jpg',
                    'alt_text' => 'Galvanised Bobman Front Load (FL) cubicle cleaner and bedding spreader',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-412-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-sweeping.jpg',
                    'alt_text' => 'Galvanised Bobman Front Load sweeping and bedding dairy cubicles',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-288-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-self-loading.jpg',
                    'alt_text' => 'Galvanised Bobman Front Load self-loading directly from a sawdust pile',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-415-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-action.jpg',
                    'alt_text' => 'Galvanised Bobman Front Load brushing stall beds in barn',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-407-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-brush-detail.jpg',
                    'alt_text' => 'Close-up of galvanised Bobman Front Load hydraulic rotary brush and scraper',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-284-scaled.jpg',
                    'filename' => 'bobman-fl-galvanised-disinfection.jpg',
                    'alt_text' => 'Filling the disinfection box on the galvanised Bobman Front Load',
                ],
            ],
        ],

        [
            'slug' => 'bobman-sl',
            'name' => 'Bobman Self Load (SL)',
            'brand_slug' => 'bobman',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 30,
            'categories' => ['cubicle-cleaners'],
            'summary' => '<p>High-capacity 1,000 L self-loading 4-in-1 cubicle cleaner and bedding machine built for larger dairy herds (200+ cubicles), capable of cleaning and bedding up to 1,000 stalls in under 20 minutes.</p>',
            'description_html' => <<<'HTML'
<h2>High-Capacity 1,000 L Self-Loading Bedding Machine for Large Herds</h2>
<p>Built for larger dairy herds (200+ cubicles), the <strong>Bobman Self Load (SL)</strong> is a heavy-duty, self-loading 4-in-1 cubicle maintenance machine with a <strong>1,000 L hopper capacity</strong>. Capable of sweeping stalls, scraping slats, and spreading bedding and disinfectant for up to 1,000 dairy cows in under 20 minutes, the Bobman SL streamlines daily routines and reduces labour across multi-shed units and robotic milking barns.</p>

<h3>Diesel &amp; Electric Configurations</h3>
<ul>
    <li><strong>Kubota 2-Cylinder Diesel:</strong> Powered by a water-cooled 13 HP Kubota 2-cylinder diesel engine with hydrostatic 3WD traction and a large fuel tank, providing high torque, a 12 km/h travel speed, and extended runtime for large setups.</li>
    <li><strong>Electric Option:</strong> Also available with a 5.5 kW electric drive motor, 2.2 kW hydraulic pump, and 8.5 kWh LiFePO4 Lithium battery (55 dB noise level, up to 120 minutes runtime).</li>
</ul>

<h3>Key Benefits &amp; Features</h3>
<ul>
    <li><strong>4-in-1 Single-Pass Efficiency:</strong> Sweeps cubicles, scrapes slatted passages, spreads bedding, and applies disinfectant powder in one pass.</li>
    <li><strong>1,000 L Self-Loading Hopper:</strong> Scoops directly from the bedding store and covers hundreds of cubicles per load.</li>
    <li><strong>Full Hydraulic Control:</strong> Equipped with a fully hydraulic rotary brush, hydraulic scraper, and front working light for clear visibility inside buildings.</li>
    <li><strong>Broad Material Compatibility:</strong> Spreads chopped straw, sawdust, wood chips, wood shavings, paper granules, separated manure fibres, lime, and disinfectant powder.</li>
    <li><strong>Compact Turning Radius:</strong> Manoeuvres cleanly around passages and crossover alleys with a 1.8–2.1 m turning radius.</li>
</ul>

<h3>Optional Tools &amp; Attachments</h3>
<ul>
    <li><strong>Disinfection Box (30 L / 40 kg):</strong> Large-capacity dispenser for applying lime or disinfectant powder during bedding.</li>
    <li><strong>Double Brush (2 × 0.7 m):</strong> Twin-brush assembly for wider, deeper cubicle sweeping.</li>
    <li><strong>Extra Mixer:</strong> Ensures uniform feeding of chopped straw.</li>
    <li><strong>Parallelogram Brush Mount:</strong> Adjusts automatically to varying stall bed heights.</li>
    <li><strong>Mineral Feed Pipe:</strong> Optional attachment for dispensing minerals onto feed.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>Bobman Self Load Diesel</th>
            <th>Bobman Self Load Electric</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine / Motor</strong></td>
            <td>Kubota 2-cylinder 13 HP (Water-cooled)</td>
            <td>5.5 kW Gear Motor + 2.2 kW Hydraulic Pump</td>
        </tr>
        <tr>
            <td><strong>Energy Source</strong></td>
            <td>Diesel (15–25 L fuel tank)</td>
            <td>LiFePO4 Lithium Battery (52.2V – 60 Ah / 8.5 kWh)</td>
        </tr>
        <tr>
            <td><strong>Drive &amp; Hydraulics</strong></td>
            <td>3WD (1× Hydrostat, 1× Gear Pump)</td>
            <td>2WD / Hydrostatic Hydraulics</td>
        </tr>
        <tr>
            <td><strong>Travel Speed</strong></td>
            <td>Up to 12 km/h</td>
            <td>Up to 12 km/h</td>
        </tr>
        <tr>
            <td><strong>Loading Method</strong></td>
            <td colspan="2">Hydraulic Self-Loading</td>
        </tr>
        <tr>
            <td><strong>Hopper Capacity</strong></td>
            <td colspan="2">1,000 L</td>
        </tr>
        <tr>
            <td><strong>Spreading Distance</strong></td>
            <td colspan="2">50 cm – 200 cm (up to 4 m)</td>
        </tr>
        <tr>
            <td><strong>Bedding Materials</strong></td>
            <td colspan="2">Chopped straw, sawdust, wood shavings/chips, manure fibres, lime, disinfectant powder</td>
        </tr>
        <tr>
            <td><strong>Brush Diameter</strong></td>
            <td colspan="2">75 – 85 cm (Optional double brush: 2 × 70 cm)</td>
        </tr>
        <tr>
            <td><strong>Scraper Width</strong></td>
            <td colspan="2">1.0 – 1.3 m</td>
        </tr>
        <tr>
            <td><strong>Dimensions (L × W × H)</strong></td>
            <td colspan="2">2.7 m × 1.2–1.3 m × 1.7 m</td>
        </tr>
        <tr>
            <td><strong>Turning Diameter / Ground Clearance</strong></td>
            <td colspan="2">1.8–2.1 m / 27 cm</td>
        </tr>
        <tr>
            <td><strong>Weight</strong></td>
            <td colspan="2">815 – 950 kg</td>
        </tr>
    </tbody>
</table>
HTML,
            'videos' => ['_-YORXByGs8', 'YeU-ikT1Qxo'],
            'preserve_existing_testimonials' => true,
            'gallery' => [
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-090-scaled.jpg',
                    'filename' => 'bobman-sl-barn-action.jpg',
                    'alt_text' => 'Bobman Self Load (SL) 1000L bedding machine operating in dairy barn',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/JDL-027-1536x1024.png',
                    'filename' => 'bobman-sl-studio.png',
                    'alt_text' => 'Bobman Self Load (SL) studio profile view',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Jydeland210521-149-scaled.jpg',
                    'filename' => 'bobman-sl-self-loading.jpg',
                    'alt_text' => 'Bobman Self Load (SL) hydraulically loading bedding material',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Bobman091025-024-scaled.jpg',
                    'filename' => 'bobman-sl-spreading.jpg',
                    'alt_text' => 'Bobman Self Load (SL) brushing cubicles and spreading bedding',
                ],
                [
                    'existing_source_path' => 'images/virtuemart/product/Bobman SL .jpg',
                    'alt_text' => 'Bobman SL cubicle cleaner and spreader',
                ],
                [
                    'existing_source_path' => 'images/virtuemart/product/Bobman SL 2.jpg',
                    'alt_text' => 'Bobman SL side view',
                ],
            ],
        ],

        [
            'slug' => 'bobman-promax',
            'name' => 'Bobman Promax',
            'brand_slug' => 'bobman',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 40,
            'categories' => ['cubicle-cleaners', 'feeders'],
            'summary' => '<p>Compact, agile 3WD multi-purpose tool carrier for scraping slats, sweeping cubicles, spreading lime/disinfectant, and pushing/mixing feed—available in Electric and Diesel versions.</p>',
            'description_html' => <<<'HTML'
<h2>Compact Multi-Purpose Tool Carrier for Cubicle Hygiene &amp; Feed Pushing</h2>
<p>The <strong>Bobman Promax</strong> is a highly manoeuvrable 3-wheel-drive tool carrier designed for tight barn passages, collecting yards, and feed tables. Using a hydraulic A-lift front arm (0–24 cm lift) and quick-change attachments, the Promax handles daily slat scraping, cubicle brushing, lime/disinfectant application, and feed pushing/turning with a single compact machine.</p>

<h3>Electric &amp; Diesel Power Options</h3>
<ul>
    <li><strong>Bobman Promax Electric (New):</strong> Powered by a quiet 4 kW electric motor and 52.2V / 60 Ah (6.1 kWh) LiFePO4 Lithium-ion battery. Ideal for indoor livestock housing, it operates at just 61 dB with zero exhaust emissions and offers battery runtime options from 60 to 90 minutes per charge.</li>
    <li><strong>Bobman Promax Diesel:</strong> Fitted with a dependable Hatz diesel engine and 5 L fuel tank, delivering up to 180 minutes of continuous runtime for larger barns and extended scraping rounds.</li>
</ul>

<h3>Key Benefits &amp; Workflow Attachments</h3>
<ul>
    <li><strong>Feed Auger / Snail:</strong> Simultaneously pushes and mixes ration along the feed barrier, improving feed intake and reducing waste and manual labour.</li>
    <li><strong>Double Front Cleaning Brush &amp; Side Brush:</strong> Sweeps cubicle beds and feeding alleys cleanly and quickly.</li>
    <li><strong>Slurry &amp; Passage Scraper (1.0 – 1.4 m):</strong> Keeps slats and crossover passages clean to support hoof health and barn hygiene.</li>
    <li><strong>Mini Powder Disinfection Dispenser (10 – 20 L):</strong> Spreads lime or disinfectant powder (10–100 cm spread distance) onto stall beds while sweeping.</li>
    <li><strong>Ultra-Compact Footprint:</strong> Only 1.5 m long and 1.0 m wide with a 1.4 m turning diameter for effortless handling in any shed layout.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>Bobman Promax Electric</th>
            <th>Bobman Promax Diesel</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Power / Engine</strong></td>
            <td>4 kW Electric Motor</td>
            <td>Hatz Diesel Engine</td>
        </tr>
        <tr>
            <td><strong>Energy Source</strong></td>
            <td>LiFePO4 Lithium Battery (52.2V – 60 Ah / 6.1 kWh)</td>
            <td>Diesel (5 L fuel tank)</td>
        </tr>
        <tr>
            <td><strong>Operating Time</strong></td>
            <td>60 – 90 min</td>
            <td>Up to 180 min</td>
        </tr>
        <tr>
            <td><strong>Drive Speed</strong></td>
            <td>Up to 10 km/h</td>
            <td>Up to 8–10 km/h</td>
        </tr>
        <tr>
            <td><strong>Noise Level</strong></td>
            <td>61 dB</td>
            <td>96 dB</td>
        </tr>
        <tr>
            <td><strong>Wheel Drive</strong></td>
            <td colspan="2">3WD</td>
        </tr>
        <tr>
            <td><strong>Front Hitch</strong></td>
            <td colspan="2">Hydraulic A-Lift Arm (0 – 24 cm)</td>
        </tr>
        <tr>
            <td><strong>Disinfection Box Volume</strong></td>
            <td colspan="2">10 L – 20 L (Lime or disinfectant powder, 10–100 cm spread)</td>
        </tr>
        <tr>
            <td><strong>Brush Diameter / Scraper Width</strong></td>
            <td colspan="2">75 – 85 cm / 1.0 – 1.4 m</td>
        </tr>
        <tr>
            <td><strong>Dimensions (L × W × H)</strong></td>
            <td colspan="2">1.5 m × 1.0 m × 1.3 m</td>
        </tr>
        <tr>
            <td><strong>Turning Diameter / Ground Clearance</strong></td>
            <td colspan="2">1.4 m / 20 cm</td>
        </tr>
        <tr>
            <td><strong>Weight</strong></td>
            <td colspan="2">350 kg</td>
        </tr>
    </tbody>
</table>
HTML,
            'videos' => ['2FQ8yN2Kdx4', 'YeU-ikT1Qxo'],
            'preserve_existing_testimonials' => false,
            'gallery' => [
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Promax-hjemmeside-2.jpg',
                    'filename' => 'bobman-promax-overview.jpg',
                    'alt_text' => 'Bobman Promax compact tool carrier with front attachment',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-341-scaled.jpg',
                    'filename' => 'bobman-promax-barn.jpg',
                    'alt_text' => 'Bobman Promax operating inside dairy barn',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-262-scaled.jpg',
                    'filename' => 'bobman-promax-cleaning.jpg',
                    'alt_text' => 'Bobman Promax sweeping and scraping passageway',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-349-scaled.jpg',
                    'filename' => 'bobman-promax-brush.jpg',
                    'alt_text' => 'Bobman Promax with rotary brush attachment',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/Nibe230523-257-scaled.jpg',
                    'filename' => 'bobman-promax-side.jpg',
                    'alt_text' => 'Bobman Promax side view in cubicle shed',
                ],
            ],
        ],

        [
            'slug' => 'bobman-madmax',
            'name' => 'Bobman Madmax',
            'brand_slug' => 'bobman',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 50,
            'categories' => ['cubicle-cleaners', 'feeders'],
            'summary' => '<p>Heavy-duty 13 HP Kubota diesel implement carrier—the big brother to the Promax—built for high-capacity feed pushing/mixing, double-brush sweeping, and passage scraping on larger farms.</p>',
            'description_html' => <<<'HTML'
<h2>Heavy-Duty Kubota Diesel Implement Carrier for Feed Pushing &amp; Barn Cleaning</h2>
<p>Positioned as the "big brother" to the Bobman Promax, the <strong>Bobman Madmax</strong> is a heavy-duty implement carrier built for larger herds and demanding workloads. Equipped with a powerful <strong>13 HP 2-cylinder water-cooled Kubota diesel engine</strong>, hydrostatic drive, and a 15 L fuel tank providing up to <strong>8 hours of continuous operation</strong>, the Madmax handles heavy feed rows and wide passageways with ease.</p>

<h3>Key Benefits &amp; Workflow Optimisation</h3>
<ul>
    <li><strong>High-Capacity Feed Pushing &amp; Mixing:</strong> Drives a 1.3 m feeding snail (auger) that pushes and remixes fresh feed along the barrier, stimulating cow intake and minimising feed waste.</li>
    <li><strong>Double Brush &amp; Heavy Scraping:</strong> Supports twin rotary brushes and wide scrapers to clean feeding tables, cubicle kerbs, and slatted alleys rapidly.</li>
    <li><strong>Kubota 2-Cylinder Water-Cooled Diesel:</strong> Delivers strong torque, 3WD hydrostatic traction, and up to 12 km/h travel speed for large farm layouts.</li>
    <li><strong>Compact Agility:</strong> Despite its heavier 450 kg build and increased pushing power, it retains a tight 1.4 m turning diameter for narrow shed crossovers.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>Bobman Madmax</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kubota 2-cylinder 13 HP (Water-cooled Diesel)</td>
        </tr>
        <tr>
            <td><strong>Fuel Tank / Runtime</strong></td>
            <td>15 L / Up to 8 hours</td>
        </tr>
        <tr>
            <td><strong>Transmission &amp; Hydraulics</strong></td>
            <td>3WD Hydrostatic (1× Hydrostat, 1× Gear Pump)</td>
        </tr>
        <tr>
            <td><strong>Travel Speed</strong></td>
            <td>Up to 12 km/h</td>
        </tr>
        <tr>
            <td><strong>Front Hitch</strong></td>
            <td>Hydraulic A-Lift Arm (0 – 20 cm)</td>
        </tr>
        <tr>
            <td><strong>Feed Pusher Width</strong></td>
            <td>1.3 m</td>
        </tr>
        <tr>
            <td><strong>Dimensions (L × W × H)</strong></td>
            <td>1.8 m × 1.1 m × 1.3 m</td>
        </tr>
        <tr>
            <td><strong>Turning Diameter / Ground Clearance</strong></td>
            <td>1.4 m / 15 cm</td>
        </tr>
        <tr>
            <td><strong>Weight</strong></td>
            <td>450 kg</td>
        </tr>
    </tbody>
</table>
HTML,
            'videos' => ['qbr9Kc8ZuzM', 'YeU-ikT1Qxo'],
            'preserve_existing_testimonials' => false,
            'gallery' => [
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2025/11/1-1536x1536.png',
                    'filename' => 'bobman-madmax-studio.png',
                    'alt_text' => 'Bobman Madmax heavy-duty implement carrier studio view',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/DSC09343-scaled.jpg',
                    'filename' => 'bobman-madmax-barn.jpg',
                    'alt_text' => 'Bobman Madmax pushing feed and cleaning in dairy barn',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/DSC09681-scaled.png',
                    'filename' => 'bobman-madmax-front.png',
                    'alt_text' => 'Bobman Madmax front attachment mount detail',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/DSC09088-scaled.png',
                    'filename' => 'bobman-madmax-side.png',
                    'alt_text' => 'Bobman Madmax side profile',
                ],
                [
                    'url' => 'https://bobman.com/wp-content/uploads/2026/01/20260130_092707000_iOS.jpg',
                    'filename' => 'bobman-madmax-action.jpg',
                    'alt_text' => 'Bobman Madmax in operation on farm',
                ],
            ],
        ],

        // =========================================================================
        // MULTIONE RANGE (8 Active Series — Obsolete 4 Series / 4.2K Excluded)
        // =========================================================================
        [
            'slug' => 'multione-1-series',
            'name' => 'MultiOne 1 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 10,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Ultra-compact, sub-400 kg mini loader featuring 3WD hydrostatic drive and a zero inside turning circle for working in narrow stables, buildings, and gardens without ground damage.</p>',
            'description_html' => <<<'HTML'
<h2>Ultra-Compact Zero-Turn Mini Loader (Model 1.1)</h2>
<p>The <strong>MultiOne 1 Series</strong> is a uniquely compact and ultra-manoeuvrable mini loader engineered for the tightest spaces. Its zero-turn steering system (0 mm inside turning radius) allows effortless handling inside traditional farm buildings, stables, polytunnels, and landscaped grounds where larger machines cannot fit.</p>
<p>Equipped with a pedal-controlled 3WD hydrostatic transmission and MultiOne's Dynamic Block System (DBS), the 1 Series applies full traction on rough ground or switches off differential lock to protect sensitive turf and surfaces when turning.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Zero-Turn Steering System:</strong> 0 / 993 mm inside/outside turning radius for unmatched agility in confined passages.</li>
    <li><strong>Sub-400 kg Gross Weight:</strong> Weighs just 398 kg while delivering a 320 kg tipping load and 1.8 m lift height.</li>
    <li><strong>3WD with DBS Traction Control:</strong> Smooth hydrostatic drive up to 8 km/h with switchable traction lock to prevent turf damage.</li>
    <li><strong>12 HP Yanmar Diesel Engine:</strong> Reliable air-cooled Yanmar L100 1-cylinder diesel engine with 19 l/min auxiliary hydraulics.</li>
    <li><strong>3-Year Manufacturer's Warranty:</strong> Backed by MultiOne's warranty and full local support from Henry Porter Machinery.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 1.1</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Yanmar L100 – 1 cylinder – 435 cc (Air-cooled)</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Fuel</strong></td>
            <td>12 HP / Diesel</td>
        </tr>
        <tr>
            <td><strong>Transmission / Drive</strong></td>
            <td>3WD Hydrostatic with DBS Traction System</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>8 km/h (5 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>19 l/min (5 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>1.80 m (70.9 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>320 kg (705 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity (with backweights)</strong></td>
            <td>250 kg (551 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>250 kg / 500 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>0 / 993 mm (0 / 39 in)</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>18×9.50-8</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>398 kg (880 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_1_1_DATASHEET__">Download MultiOne 1.1 Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_1_SERIES_LOAD_CHART__">Download MultiOne 1 Series Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>Transform your MultiOne into a bucket loader, grapple, auger, mower, sweeper, or bale handler in seconds with MultiOne's rapid attachment coupling system. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['mmQGWKK5ve0'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_1_1_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/1.1_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-1-1-datasheet.pdf',
                    'title' => 'MultiOne 1.1 Technical Data Sheet',
                ],
                '__PDF_1_SERIES_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042453-R01-LOAD-CHART-1-SERIES-compressed.pdf',
                    'filename' => 'multione-1-series-load-chart.pdf',
                    'title' => 'MultiOne 1 Series Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-1-series-mini-loader-overview-1.jpg',
                    'filename' => 'multione-1-series-overview.jpg',
                    'alt_text' => 'MultiOne 1 Series compact zero-turn mini loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/91A1002-1.jpg',
                    'filename' => 'multione-1-series-bucket.jpg',
                    'alt_text' => 'MultiOne 1 Series mini loader working with front bucket',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/91A0989-1.jpg',
                    'filename' => 'multione-1-series-action.jpg',
                    'alt_text' => 'MultiOne 1 Series manoeuvring in tight space',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/91A0331-1.jpg',
                    'filename' => 'multione-1-series-mower.jpg',
                    'alt_text' => 'MultiOne 1 Series operating mower attachment',
                ],
            ],
        ],

        [
            'slug' => 'multione-2-series',
            'name' => 'MultiOne 2 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 20,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Light, powerful 4WD articulated mini loader (2.3 EFi) delivering an impressive 700 kg tipping load from a 710 kg machine with 42 l/min hi-flow auxiliary hydraulics.</p>',
            'description_html' => <<<'HTML'
<h2>Light &amp; Powerful 4WD Articulated Mini Loader (Model 2.3 EFi)</h2>
<p>The <strong>MultiOne 2 Series (2.3 EFi)</strong> is the smallest and most economical 4WD articulated mini loader in the MultiOne lineup, engineered to deliver an exceptional lift-to-weight ratio. Weighing just 710 kg, it achieves a 700 kg tipping load (560 kg straight lifting capacity with backweights) while remaining light enough for effortless transport on smaller trailers.</p>
<p>Powered by a 26.5 HP Kohler electronic fuel-injected (EFI) engine paired with 42 l/min high-flow auxiliary hydraulics and MultiOne's DBS differential lock system, the 2 Series is an ideal skid-steer alternative for small farms, equestrian yards, estates, and groundcare contractors.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>4WD Hydrostatic Transmission with DBS:</strong> Confident all-terrain traction up to 15 km/h with switchable differential lock to protect lawns and paddocks.</li>
    <li><strong>Hi-Flow Auxiliary Hydraulics (42 l/min):</strong> Powers demanding hydraulic attachments including flail mowers, augers, sweepers, and grabs.</li>
    <li><strong>26.5 HP Kohler EFI Engine:</strong> Twin-cylinder electronic fuel injection engine offering strong torque (55.5 Nm), easy cold starting, and low fuel consumption.</li>
    <li><strong>Exceptional Lift-to-Weight Ratio:</strong> 700 kg tipping load at only 710 kg machine weight, with a tight 700 mm inside turning radius.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 2.3 EFi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kohler EFI ECV749 – 2 cylinder – 747 cc (Air-cooled)</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V &amp; EPA/CARB Phase III</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>26.5 HP / 55.5 Nm @ 2,600 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel</strong></td>
            <td>Petrol (Electronic Fuel Injection)</td>
        </tr>
        <tr>
            <td><strong>Transmission / Drive</strong></td>
            <td>4WD Hydrostatic with DBS Differential Lock</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>15 km/h (9.3 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>42 l/min (11.1 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>1.60 m (63 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>700 kg (1,543 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>560 kg (1,235 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>840 kg / 770 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>700 / 1,698 mm (27.6 / 66.9 in)</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>20×8.00-10</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>710 kg (1,570 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_2_3_DATASHEET__">Download MultiOne 2.3 EFi Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_2_SERIES_LOAD_CHART__">Download MultiOne 2 Series Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>Equip your MultiOne 2 Series for mucking out, bale handling, mowing, fencing, or landscaping with over 170 compatible attachments. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['Qmo5Bji9Ljc', 'fAiL4fvAVr0'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_2_3_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/2.3_EFI_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-2-3-efi-datasheet.pdf',
                    'title' => 'MultiOne 2.3 EFi Technical Data Sheet',
                ],
                '__PDF_2_SERIES_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042477-R01-LOAD-CHART-2-SERIES-EFI-2%C2%B0-gen-compressed.pdf',
                    'filename' => 'multione-2-series-load-chart.pdf',
                    'title' => 'MultiOne 2 Series Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-2-series-mini-loader-overview-1.jpg',
                    'filename' => 'multione-2-series-overview.jpg',
                    'alt_text' => 'MultiOne 2 Series 2.3 EFi articulated mini loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-2-series-mini-loader-gallery-1.jpg',
                    'filename' => 'multione-2-series-gallery-1.jpg',
                    'alt_text' => 'MultiOne 2 Series working on farm and estate tasks',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-2-series-mini-loader-gallery-2.jpg',
                    'filename' => 'multione-2-series-gallery-2.jpg',
                    'alt_text' => 'MultiOne 2 Series loading with bucket attachment',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-2-series-mini-loader-gallery-3.jpg',
                    'filename' => 'multione-2-series-gallery-3.jpg',
                    'alt_text' => 'MultiOne 2 Series compact articulated steering in action',
                ],
            ],
        ],

        [
            'slug' => 'multione-5-series',
            'name' => 'MultiOne 5 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 30,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Compact telescopic-boom powerhouse (5.2K iD &amp; 5.3K iD) with Stage V diesel engines, up to 1,290 kg tipping load, 2.75 m lift height, and up to 60 l/min auxiliary hydraulics.</p>',
            'description_html' => <<<'HTML'
<h2>Compact Telescopic-Boom Powerhouse (Models 5.2K iD &amp; 5.3K iD)</h2>
<p>The redesigned <strong>MultiOne 5 Series (5.2K iD and 5.3K iD)</strong> brings MultiOne's signature <strong>Double-H self-levelling telescopic boom</strong> (2.75 m lift height) into a compact, lightweight chassis. Featuring the updated <em>Innovation Design (iD)</em> exterior, an enlarged operator station (+5 cm), a redesigned anti-glare digital display, and an upgraded multi-function joystick on the 5.3K iD, the 5 Series is a versatile attachment powerhouse for farms, yards, and contractors.</p>
<p>Choose between the economical 25 HP Kubota-powered <strong>5.2K iD</strong> (42 l/min flow, 1,110 kg tipping load) or the high-torque 25 HP Kohler-powered <strong>5.3K iD</strong> delivering 90 Nm of torque at 1,800 rpm, a boosted <strong>60 l/min hydraulic flow</strong>, and a 1,290 kg tipping load.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Self-Levelling Telescopic Boom (2.75 m):</strong> Reaches high over trailer sides, feeders, and walls while keeping loads level automatically.</li>
    <li><strong>High-Torque Stage V Diesel Engines:</strong> 25 HP Kubota D902 (5.2K iD) or 25 HP / 90 Nm Kohler KSD 1403 (5.3K iD) requiring no DPF or AdBlue.</li>
    <li><strong>Boosted 60 l/min Hydraulics (5.3K iD):</strong> Drives high-demand hydraulic attachments effortlessly via the multi-function joystick.</li>
    <li><strong>Easy Trailer Transport:</strong> Weighs just 1,020–1,070 kg for straightforward towing between yards and sites.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 5.2K iD</th>
            <th>MultiOne 5.3K iD</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kubota D902 (3 cyl)</td>
            <td>Kohler KSD 1403 NA (3 cyl)</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V / Tier 4 Final</td>
            <td>Stage V / Tier 4 Final</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>25 HP / 55 Nm @ 2,400 rpm</td>
            <td>25 HP / 90 Nm @ 1,800 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel / Transmission</strong></td>
            <td>Diesel / 4WD Hydrostatic</td>
            <td>Diesel / 4WD Hydrostatic</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>14 km/h (8.7 mph)</td>
            <td>12.5 km/h (7.8 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>42 l/min (11 GPM)</td>
            <td>60 l/min (13.2 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>2.75 m (108 in)</td>
            <td>2.75 m (108 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>1,110 kg (2,447 lbs)</td>
            <td>1,290 kg (2,844 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>890 kg (1,962 lbs)</td>
            <td>1,030 kg (2,270 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>800 kg / 900 kgf</td>
            <td>800 kg / 900 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>890 / 1,890 mm</td>
            <td>800 / 2,000 mm</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>23×8.50-12</td>
            <td>23×10.50-12 ET-25</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>1,020 kg (2,250 lbs)</td>
            <td>1,070 kg (2,360 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_5_2_DATASHEET__">Download MultiOne 5.2K iD Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_5_3_DATASHEET__">Download MultiOne 5.3K iD Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_5_2_LOAD_CHART__">Download MultiOne 5.2K iD Load Chart (PDF)</a></li>
    <li><a href="__PDF_5_3_LOAD_CHART__">Download MultiOne 5.3K iD Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>Pair the 5 Series' telescopic boom and hi-flow hydraulics with over 170 genuine MultiOne attachments for farming, groundcare, property maintenance, and construction. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['xHkTdof8cQw'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_5_2_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/5.2_K_iD_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-5-2k-id-datasheet.pdf',
                    'title' => 'MultiOne 5.2K iD Technical Data Sheet',
                ],
                '__PDF_5_3_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/5.3iD-with-Kohler-datasheet-EN.pdf',
                    'filename' => 'multione-5-3-id-datasheet.pdf',
                    'title' => 'MultiOne 5.3K iD Technical Data Sheet',
                ],
                '__PDF_5_2_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/C042562-LOAD-CHART-5.2K-iD.pdf',
                    'filename' => 'multione-5-2k-id-load-chart.pdf',
                    'title' => 'MultiOne 5.2K iD Load Chart',
                ],
                '__PDF_5_3_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/C042553_R00_LOAD-CHART_5.3iD.pdf',
                    'filename' => 'multione-5-3-id-load-chart.pdf',
                    'title' => 'MultiOne 5.3K iD Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/5.3iD_trequarti_benna-1.png',
                    'filename' => 'multione-5-series-studio.png',
                    'alt_text' => 'MultiOne 5.3K iD articulated mini loader studio view',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/Multione_5.3_bucket-1030x688-1030x688-1.jpg',
                    'filename' => 'multione-5-series-bucket.jpg',
                    'alt_text' => 'MultiOne 5 Series working with front bucket',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/Multione_5.3_flail_mower_boom-1030x688-1030x688-1.jpg',
                    'filename' => 'multione-5-series-flail-mower.jpg',
                    'alt_text' => 'MultiOne 5 Series operating boom flail mower',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2021/12/Multione_5.3_top-1030x688-1030x688-1.jpg',
                    'filename' => 'multione-5-series-top-view.jpg',
                    'alt_text' => 'MultiOne 5 Series articulated chassis and operator cockpit',
                ],
            ],
        ],

        [
            'slug' => 'multione-6-series',
            'name' => 'MultiOne 6 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 40,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Entry into the heavy-duty MultiOne range (6.3 iDS), combining a 1,400 kg tipping load, 2.92 m self-levelling telescopic boom, 60 l/min hydraulics, and 2-speed 23 km/h drive.</p>',
            'description_html' => <<<'HTML'
<h2>Heavy-Duty Compact Telescopic Loader (Model 6.3 iDS)</h2>
<p>The <strong>MultiOne 6 Series (6.3 iDS)</strong> marks the entry point into MultiOne's heavy-duty articulated range. Combining a super-strong <strong>Double-H self-levelling telescopic boom</strong> (2.92 m lift height) with a 1,400 kg tipping load (1,120 kg straight lifting capacity with backweights) and a low 1,470 kg gross weight, the 6.3 iDS handles serious agricultural and construction work while remaining easy to tow on a plant trailer alongside multiple attachments.</p>
<p>Its 1,391 cc Kohler KSD 1403 Stage V diesel engine delivers 90 Nm of torque from just 1,800 rpm—the highest torque density in the sub-19 kW class—requiring no exhaust after-treatment (no DPF or AdBlue).</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Self-Levelling Double-H Telescopic Boom:</strong> Lifts up to 2.92 m with 1,400 kg breakout force and automatic load levelling.</li>
    <li><strong>2-Speed Hydrostatic Drive (Up to 23 km/h):</strong> Fast travel across yards and fields with 2,050 kgf pushing force.</li>
    <li><strong>60 l/min Hi-Flow Auxiliary Hydraulics:</strong> Runs demanding hydraulic tools continuously.</li>
    <li><strong>No DPF or AdBlue Required:</strong> High-torque 25 HP / 90 Nm Kohler KSD 1403 Stage V engine keeps servicing simple.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 6.3 iDS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kohler KSD 1403 NA – 3 cylinder – 1,391 cc</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V / Tier 4 Final (No DPF / No AdBlue)</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>25 HP / 90 Nm @ 1,800 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel / Transmission</strong></td>
            <td>Diesel / 4WD Hydrostatic (2-Speed)</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>23 km/h (14 mph) – 2 Speed</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>60 l/min (15.8 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>2.92 m (115 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>1,400 kg (3,086 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>1,120 kg (2,469 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>1,400 kg / 2,050 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>905 / 2,020 mm (35.6 / 79.5 in)</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>23×8.50-12</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>1,470 kg (3,090 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_6_3_DATASHEET__">Download MultiOne 6.3 iDS Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_6_SERIES_LOAD_CHART__">Download MultiOne 6 Series Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>From silage grabs and pallet forks to rotary brooms, post drivers, and snow blades, over 170 attachments fit the MultiOne 6 Series. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['p7HzQCWQKFo', 'AlUvyi3-KP4'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_6_3_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/6.3_iDS_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-6-3-ids-datasheet.pdf',
                    'title' => 'MultiOne 6.3 iDS Technical Data Sheet',
                ],
                '__PDF_6_SERIES_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042456-R02-LOAD-CHART-6-SERIES-1.pdf',
                    'filename' => 'multione-6-series-load-chart.pdf',
                    'title' => 'MultiOne 6 Series Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/6.jpeg',
                    'filename' => 'multione-6-series-profile.jpg',
                    'alt_text' => 'MultiOne 6 Series 6.3 iDS compact telescopic loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-6k-series-bucket_01-1030x687-1.jpeg',
                    'filename' => 'multione-6-series-bucket.jpg',
                    'alt_text' => 'MultiOne 6 Series working with front loader bucket',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-6-series-with-rotary-broom-1030x688-1.jpg',
                    'filename' => 'multione-6-series-rotary-broom.jpg',
                    'alt_text' => 'MultiOne 6 Series sweeping yard with rotary broom attachment',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-6-series-with-flail-mower-whit-side-shift1-1030x688-1.jpg',
                    'filename' => 'multione-6-series-flail-mower.jpg',
                    'alt_text' => 'MultiOne 6 Series operating side-shift flail mower',
                ],
            ],
        ],

        [
            'slug' => 'multione-7-series',
            'name' => 'MultiOne 7 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 50,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Best-selling heavy-lifting all-rounder (7.2K) with integrated backweights, a 1,725 kg tipping load (1,380 kg straight lift), 2.90 m telescopic boom, and Kubota D1305 Stage V diesel engine.</p>',
            'description_html' => <<<'HTML'
<h2>Best-Selling Heavy-Lifting Compact Loader (Model 7.2K)</h2>
<p>The <strong>MultiOne 7 Series (7.2K)</strong> is one of the best-selling loaders in the range, engineered specifically as a cost-effective "Lifter &amp; Shifter" for farms, builders, and estates that prioritise maximum lifting capacity. Built on the heavy-duty chassis with <strong>integrated rear counterweights</strong> and direct-drive radial piston wheel motors, the 7.2K delivers a massive <strong>1,725 kg tipping load</strong> and <strong>1,380 kg straight lifting capacity</strong> at a 2.90 m lift height.</p>
<p>Powered by a 3-cylinder 1,261 cc Kubota D1305 Stage V diesel engine producing 85 Nm of torque at just 1,700 rpm, the 7.2K offers dependable, straightforward mechanical operation with 1,700 kg breakout force.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Integrated Backweights as Standard:</strong> Maximises lifting stability for handling big bales, silage, fertilizer bags, and palletised loads (1,380 kg straight lift).</li>
    <li><strong>Kubota D1305 Stage V Engine (1,261 cc):</strong> Proven 25 HP high-torque diesel engine with no DPF or AdBlue required.</li>
    <li><strong>Direct-Drive Radial Piston Motors:</strong> High torque and 1,690 kgf pushing force on rough farm tracks and yards.</li>
    <li><strong>2.90 m Self-Levelling Telescopic Boom:</strong> High reach and 1,700 kg breakout force for loading trailers and feeders.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 7.2K</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kubota D1305 – 3 cylinder – 1,261 cc</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V / Tier 4 Final</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>25 HP / 85 Nm @ 1,700 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel / Transmission</strong></td>
            <td>Diesel / 4WD Hydrostatic (Direct-drive radial piston motors)</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>9 km/h (5.6 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>40 l/min (10.5 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>2.90 m (114 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>1,725 kg (3,803 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>1,380 kg (3,042 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>1,700 kg / 1,690 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>825 / 2,090 mm (32.5 / 82.3 in)</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>23×10.50-12 ET-25</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>1,590 kg (3,510 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_7_2_DATASHEET__">Download MultiOne 7.2K Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_7_2_LOAD_CHART__">Download MultiOne 7.2K Lifting Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>Switch seamlessly between bale spikes, silage grabs, multipurpose buckets, pallet forks, sweepers, and backhoes. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['LDgEaeQZZ98'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_7_2_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/7.2_K_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-7-2k-datasheet.pdf',
                    'title' => 'MultiOne 7.2K Technical Data Sheet',
                ],
                '__PDF_7_2_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/7.2K-Lifting-Chart.pdf',
                    'filename' => 'multione-7-2k-lifting-chart.pdf',
                    'title' => 'MultiOne 7.2K Lifting Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/7.jpeg',
                    'filename' => 'multione-7-series-profile.jpg',
                    'alt_text' => 'MultiOne 7 Series 7.2K heavy-lifting compact loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/09/multione-agriculture-small-holders-application.jpg',
                    'filename' => 'multione-7-series-agriculture.jpg',
                    'alt_text' => 'MultiOne 7 Series handling bales on farm',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-7-series-with-multipurpose-bucket1-1030x688-1.jpg',
                    'filename' => 'multione-7-series-bucket.jpg',
                    'alt_text' => 'MultiOne 7 Series with multipurpose bucket',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-7-series-with-sweeper1-1030x688-1.jpg',
                    'filename' => 'multione-7-series-sweeper.jpg',
                    'alt_text' => 'MultiOne 7 Series operating rotary collector sweeper',
                ],
            ],
        ],

        [
            'slug' => 'multione-8-series',
            'name' => 'MultiOne 8 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 60,
            'categories' => ['compact-loaders'],
            'summary' => '<p>High-performance professional loaders (8.4 TurboS &amp; 8.5SK) featuring Turbo diesel engines up to 57 HP, OneDrive hydrostatic transmission with cruise control, up to 83 l/min oil flow, and 3.2 m lift height.</p>',
            'description_html' => <<<'HTML'
<h2>High-Performance Turbo Articulated Loaders (Models 8.4 TurboS &amp; 8.5SK)</h2>
<p>The <strong>MultiOne 8 Series (8.4 TurboS and 8.5SK)</strong> represents the core of MultiOne's high-performance professional range. Engineered with a low centre of gravity, direct-drive wheel motors, and the advanced <strong>OneDrive hydrostatic transmission with cruise control</strong>, the 8 Series combines high travel speeds (up to 26 km/h), massive auxiliary oil flow (74–83 l/min), and tipping loads up to 2,050 kg.</p>
<p>Choose between the <strong>8.4 TurboS</strong> (25 HP Turbo Intercooler delivering 120 Nm at 1,400 rpm with no DPF/AdBlue required) or the <strong>8.5SK</strong> powered by a 57 HP / 225 Nm Kohler KDI 1903 Turbo common-rail engine with a 3.2 m telescopic boom lift height. Both models are available with an optional enclosed cab and air conditioning.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Turbocharged Engine Options:</strong> 25 HP / 120 Nm Turbo Intercooler (8.4 TurboS) or 57 HP / 225 Nm Turbo Common-Rail (8.5SK).</li>
    <li><strong>OneDrive Hydrostatic Transmission:</strong> Optimised hydraulic drive efficiency with 2-speed travel (up to 25–26 km/h) and standard cruise control.</li>
    <li><strong>High-Flow Auxiliary Hydraulics (74 – 83 l/min):</strong> Runs heavy-duty forestry, agricultural, and groundworks attachments at peak output.</li>
    <li><strong>Up to 3.2 m Lift Height &amp; 2,050 kg Tipping Load:</strong> High reach and lifting capacity (1,470–1,640 kg straight lift with backweights).</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 8.4 TurboS</th>
            <th>MultiOne 8.5SK</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kohler/Rehlko KSD 1403-TCA Turbo Intercooler</td>
            <td>Kohler KDI 1903 – 3 cyl – 1,861 cc Turbo Common Rail</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V / Tier 4 Final</td>
            <td>Stage V</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>25 HP / 120 Nm @ 1,400 rpm</td>
            <td>57 HP / 225 Nm @ 1,500 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel / Transmission</strong></td>
            <td>Diesel / Hydrostatic OneDrive (2-Speed)</td>
            <td>Diesel / Hydrostatic OneDrive (2-Speed)</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>12.5 – 25 km/h (7.8 – 15.5 mph)</td>
            <td>13 – 26 km/h (8 – 16 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>74 l/min (19.5 GPM)</td>
            <td>83 l/min (21.9 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>2.98 m (117.3 in)</td>
            <td>3.20 m (126 in)</td>
        </tr>
        <tr>
            <td><strong>Max Tipping Load (with backweights)</strong></td>
            <td>1,840 kg (4,056 lbs)</td>
            <td>2,050 kg (4,520 lbs)</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>1,470 kg (3,240 lbs)</td>
            <td>1,640 kg (3,615 lbs)</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>1,700 kg / 2,050 kgf</td>
            <td>1,700 kg / 2,190 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>825 / 2,090 mm</td>
            <td>820 / 2,295 mm</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>26×12.00-12 ET-50</td>
            <td>400/50-15 (31×15.50-15) ET-100</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>1,680 kg (3,710 lbs)</td>
            <td>1,790 kg (3,946 lbs)</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_8_4_DATASHEET__">Download MultiOne 8.4 TurboS Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_8_5_DATASHEET__">Download MultiOne 8.5SK Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_8_4_LOAD_CHART__">Download MultiOne 8.4 TurboS Load Chart (PDF)</a></li>
    <li><a href="__PDF_8_5_LOAD_CHART__">Download MultiOne 8.5SK Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>With up to 83 l/min auxiliary flow and optional ACI joystick control, the 8 Series powers over 170 attachments from power harrows and flail mowers to heavy pallet forks and grabs. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['apcxenaeRSM', 'hZ_jRi2ARuM'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_8_4_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/A07_8_series_multione_mini_loader_datasheet-1.pdf',
                    'filename' => 'multione-8-4-turbos-datasheet.pdf',
                    'title' => 'MultiOne 8.4 TurboS Technical Data Sheet',
                ],
                '__PDF_8_5_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/A19_8.5_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-8-5sk-datasheet.pdf',
                    'title' => 'MultiOne 8.5SK Technical Data Sheet',
                ],
                '__PDF_8_4_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042555_R00_LOAD-CHART_8.4TurboS.pdf',
                    'filename' => 'multione-8-4-turbos-load-chart.pdf',
                    'title' => 'MultiOne 8.4 TurboS Load Chart',
                ],
                '__PDF_8_5_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042529-R00-LOAD-CHART-M1-8.5SK.pdf',
                    'filename' => 'multione-8-5sk-load-chart.pdf',
                    'title' => 'MultiOne 8.5SK Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/8.jpeg',
                    'filename' => 'multione-8-series-profile.jpg',
                    'alt_text' => 'MultiOne 8 Series high-performance articulated loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/multione-8-series-mini-loader.jpg',
                    'filename' => 'multione-8-series-overview.jpg',
                    'alt_text' => 'MultiOne 8 Series working with heavy load',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-8-series-with-pallet-fork1-1030x688-1.jpg',
                    'filename' => 'multione-8-series-pallet-fork.jpg',
                    'alt_text' => 'MultiOne 8 Series lifting with pallet forks',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-8-series-with-flail-mower-with-side-shift1-1030x688-1.jpg',
                    'filename' => 'multione-8-series-flail-mower.jpg',
                    'alt_text' => 'MultiOne 8 Series operating side-shift flail mower',
                ],
            ],
        ],

        [
            'slug' => 'multione-11-series',
            'name' => 'MultiOne 11 Series',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 70,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Flagship heavy-duty compact loaders (11.6K TurboS &amp; 11.9K TurboS) delivering up to 75 HP, 3.7 m lift height, and 3,280 kg tipping load while remaining transportable on a 3.5T plant trailer.</p>',
            'description_html' => <<<'HTML'
<h2>Flagship Heavy-Duty Compact Loaders (Models 11.6K TurboS &amp; 11.9K TurboS)</h2>
<p>The <strong>MultiOne 11 Series (11.6K TurboS and 11.9K TurboS)</strong> sits at the top of the MultiOne range, delivering best-in-class lifting capacity (up to <strong>3,280 kg tipping load / 2,620 kg straight lift</strong>), a <strong>3.70 m max lifting height</strong>, and up to <strong>75 HP / 300 Nm</strong> of common-rail Turbo diesel performance—all while keeping unladen weight at just 2,320–2,400 kg so the machine can still be legally transported on a 3.5-tonne plant trailer.</p>
<p>The latest <em>TurboS</em> evolution introduces a redesigned hydrostatic pump and upgraded wheel motors within the OneDrive system, delivering 3,250 kgf pushing force, 2-speed travel up to 30 km/h, and up to 95 l/min auxiliary hydraulic flow.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>Class-Leading Lift &amp; Reach:</strong> 3.70 m telescopic boom height with up to 3,280 kg tipping load (2,620 kg straight lift with backweights).</li>
    <li><strong>Transportable by 3.5T Plant Trailer:</strong> 2,320 kg (11.6K TurboS) to 2,400 kg (11.9K TurboS) gross weight.</li>
    <li><strong>57 HP or 75 HP Kohler Turbo Engines:</strong> High-torque Stage V common-rail diesel engines (225 Nm on 11.6K; 300 Nm on 11.9K).</li>
    <li><strong>Up to 95 l/min Hydraulics &amp; 30 km/h OneDrive:</strong> Two-speed hydrostatic transmission with cruise control and high-flow hydraulics.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne 11.6K TurboS</th>
            <th>MultiOne 11.9K TurboS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Engine</strong></td>
            <td>Kohler KDI 1903 – 3 cyl – 1,861 cc Turbo Common Rail</td>
            <td>Kohler KDI 2504 – 4 cyl – 2,482 cc Turbo Common Rail</td>
        </tr>
        <tr>
            <td><strong>Emission Standard</strong></td>
            <td>Stage V</td>
            <td>Stage V</td>
        </tr>
        <tr>
            <td><strong>Power (ISO Gross) / Torque</strong></td>
            <td>57 HP / 225 Nm @ 1,500 rpm</td>
            <td>75 HP / 300 Nm @ 1,500 rpm</td>
        </tr>
        <tr>
            <td><strong>Fuel / Transmission</strong></td>
            <td>Diesel / Hydrostatic OneDrive (2-Speed)</td>
            <td>Diesel / Hydrostatic OneDrive (2-Speed)</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed (2-Speed)</strong></td>
            <td>15 – 30 km/h (9.3 – 18.6 mph)</td>
            <td>15 – 30 km/h (10.5 – 18.6 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>83 l/min</td>
            <td>95 l/min</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>3.70 m</td>
            <td>3.70 m</td>
        </tr>
        <tr>
            <td><strong>Max Tipping Load (with backweights)</strong></td>
            <td>3,180 kg</td>
            <td>3,280 kg</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>2,540 kg</td>
            <td>2,620 kg</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>3,000 kg / 3,250 kgf</td>
            <td>3,150 kg / 3,250 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>1,135 / 2,635 mm</td>
            <td>1,135 / 2,635 mm</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>380/55-17</td>
            <td>380/55-17</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>2,320 kg</td>
            <td>2,400 kg</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_11_6_DATASHEET__">Download MultiOne 11.6K TurboS Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_11_9_DATASHEET__">Download MultiOne 11.9K TurboS Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_11_SERIES_LOAD_CHART__">Download MultiOne 11 Series Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>Equip the 11 Series with high-capacity buckets, silage grabs, bale handlers, forestry mulchers, and construction attachments. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['XFnLzA1F5HU'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_11_6_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/11.6_K_TurboS_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-11-6k-turbos-datasheet.pdf',
                    'title' => 'MultiOne 11.6K TurboS Technical Data Sheet',
                ],
                '__PDF_11_9_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/11.9_K_TurboS_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-11-9k-turbos-datasheet.pdf',
                    'title' => 'MultiOne 11.9K TurboS Technical Data Sheet',
                ],
                '__PDF_11_SERIES_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042514-R00-LOAD-CHART-M1-11-SERIES.pdf',
                    'filename' => 'multione-11-series-load-chart.pdf',
                    'title' => 'MultiOne 11 Series Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/11.jpeg',
                    'filename' => 'multione-11-series-profile.jpg',
                    'alt_text' => 'MultiOne 11 Series flagship heavy-duty compact loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/12/11.6K_trequarti_dx_benna-copia-1030x688-1.jpg',
                    'filename' => 'multione-11-6k-bucket.jpg',
                    'alt_text' => 'MultiOne 11.6K TurboS with front bucket',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/10/HD-Flail-Mower-right-side-view-1030x687-1.jpg',
                    'filename' => 'multione-11-series-hd-flail-mower.jpg',
                    'alt_text' => 'MultiOne 11 Series operating heavy-duty flail mower',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/11.6_quote_500x800-copia.jpg',
                    'filename' => 'multione-11-series-dimensions.jpg',
                    'alt_text' => 'MultiOne 11 Series dimensions diagram',
                ],
            ],
        ],

        [
            'slug' => 'multione-ez-series',
            'name' => 'MultiOne EZ Series (Emission Zero)',
            'brand_slug' => 'multione',
            'condition' => 'new',
            'status' => 'published',
            'sort_order' => 80,
            'categories' => ['compact-loaders'],
            'summary' => '<p>Fully electric Emission Zero compact loaders (EZ5 &amp; EZ8) powered by LiFePO4 Lithium-ion batteries with onboard and optional rapid charging, delivering zero emissions and near-silent operation.</p>',
            'description_html' => <<<'HTML'
<h2>Full-Electric Emission Zero Compact Loaders (Models EZ5 &amp; EZ8)</h2>
<p>The <strong>MultiOne EZ Series (Emission Zero)</strong> brings full-electric, zero-emission power to the proven 5 Series and 8 Series chassis designs. Powered by dual electric motors and high-capacity <strong>LiFePO4 Lithium-ion batteries</strong> (with standard onboard charging and optional external fast-charging stations), the EZ5 and EZ8 match the lifting and hydraulic capability of their diesel counterparts while operating with zero exhaust fumes and minimal noise—making them ideal for working inside livestock sheds, stables, glasshouses, warehouses, and noise-sensitive sites.</p>

<h3>Key Features</h3>
<ul>
    <li><strong>100% Electric, Zero Emissions:</strong> Dual electric motors (one for drive traction, one for auxiliary hydraulics) for efficient energy management.</li>
    <li><strong>Standard or Long-Range LiFePO4 Lithium Batteries:</strong> 10.8 kWh or 14.6 kWh on the EZ5; 21.5 kWh or 29.2 kWh on the EZ8.</li>
    <li><strong>Onboard &amp; Optional Rapid Charging:</strong> Built-in charger plugs into standard site power, with optional fast-charging stations available.</li>
    <li><strong>Full Telescopic Reach &amp; Lift:</strong> 2.75 m lift / 1,110 kg tipping load on the EZ5; 2.98 m lift / 1,780 kg tipping load on the EZ8.</li>
</ul>

<h3>Technical Specifications</h3>
<table>
    <thead>
        <tr>
            <th>Specification</th>
            <th>MultiOne EZ5</th>
            <th>MultiOne EZ8</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Drive System</strong></td>
            <td>2 Electric Motors (Full Electric)</td>
            <td>2 Electric Motors (Full Electric)</td>
        </tr>
        <tr>
            <td><strong>Battery Capacity (LiFePO4)</strong></td>
            <td>10.8 kWh (Standard) or 14.6 kWh (Long Range)</td>
            <td>21.5 kWh (Standard) or 29.2 kWh (Long Range)</td>
        </tr>
        <tr>
            <td><strong>Transmission</strong></td>
            <td>4WD Hydrostatic</td>
            <td>4WD Hydrostatic</td>
        </tr>
        <tr>
            <td><strong>Max Drive Speed</strong></td>
            <td>12 km/h (7.5 mph)</td>
            <td>12 km/h (7.5 mph)</td>
        </tr>
        <tr>
            <td><strong>Auxiliary Hydraulic Oil Flow</strong></td>
            <td>35 l/min (9.3 GPM)</td>
            <td>35 l/min (9.3 GPM)</td>
        </tr>
        <tr>
            <td><strong>Max Lifting Height</strong></td>
            <td>2.75 m (108 in)</td>
            <td>2.98 m (114 in)</td>
        </tr>
        <tr>
            <td><strong>Tipping Load (with backweights)</strong></td>
            <td>1,110 kg (2,447 lbs)</td>
            <td>1,780 kg</td>
        </tr>
        <tr>
            <td><strong>Lifting Capacity Straight (with backweights)</strong></td>
            <td>890 kg (1,962 lbs)</td>
            <td>1,420 kg</td>
        </tr>
        <tr>
            <td><strong>Breakout Force (50 cm) / Pushing Force</strong></td>
            <td>800 kg / 900 kgf</td>
            <td>1,700 kg / 2,050 kgf</td>
        </tr>
        <tr>
            <td><strong>Turning Radius (Inside / Outside)</strong></td>
            <td>800 / 2,000 mm</td>
            <td>825 / 2,090 mm</td>
        </tr>
        <tr>
            <td><strong>Standard Tyres (Tractor Profile)</strong></td>
            <td>23×10.50-12 ET-25</td>
            <td>26×12.00-12</td>
        </tr>
        <tr>
            <td><strong>Unladen Weight</strong></td>
            <td>1,020 kg (2,250 lbs)</td>
            <td>1,570 kg</td>
        </tr>
    </tbody>
</table>

<h3>Data Sheets &amp; Load Charts</h3>
<ul>
    <li><a href="__PDF_EZ5_DATASHEET__">Download MultiOne EZ5 &amp; EZ5 Long Range Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_EZ8_DATASHEET__">Download MultiOne EZ8 Technical Data Sheet (PDF)</a></li>
    <li><a href="__PDF_EZ5_LOAD_CHART__">Download MultiOne EZ5 Load Chart (PDF)</a></li>
    <li><a href="__PDF_EZ8_LOAD_CHART__">Download MultiOne EZ8 Load Chart (PDF)</a></li>
</ul>

<h3>Over 170 Attachments Available</h3>
<p>All standard MultiOne attachments—from buckets, grabs, and pallet forks to sweepers, weed brushes, and hedge cutters—are compatible with the EZ Series. <a href="https://www.multione.com/attachments/">Explore the full range of MultiOne attachments on multione.com &rarr;</a></p>
<p>We always have at least one loader in stock available to test drive in our yard or in your own surroundings. Call our Lisburn team on <a href="tel:+442892622611">028 9262 2611</a> for more information.</p>
HTML,
            'videos' => ['ryjAtFmww2g', 'KOeLqNt7VUA'],
            'copy_multione_testimonial' => true,
            'pdfs' => [
                '__PDF_EZ5_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/EZ5_EZ5_LONG_RANGE_multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-ez5-datasheet.pdf',
                    'title' => 'MultiOne EZ5 Technical Data Sheet',
                ],
                '__PDF_EZ8_DATASHEET__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/EZ8_EZ8__multione_mini_loader_datasheet.pdf',
                    'filename' => 'multione-ez8-datasheet.pdf',
                    'title' => 'MultiOne EZ8 Technical Data Sheet',
                ],
                '__PDF_EZ5_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042537-R00-LOAD-CHART-M1-EZ5.pdf',
                    'filename' => 'multione-ez5-load-chart.pdf',
                    'title' => 'MultiOne EZ5 Load Chart',
                ],
                '__PDF_EZ8_LOAD_CHART__' => [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/C042481-R02-LOAD-CHART-M1-EZ8.pdf',
                    'filename' => 'multione-ez8-load-chart.pdf',
                    'title' => 'MultiOne EZ8 Load Chart',
                ],
            ],
            'gallery' => [
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/EZ5.jpeg',
                    'filename' => 'multione-ez5-profile.jpg',
                    'alt_text' => 'MultiOne EZ5 Emission Zero electric compact loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/EZ5_header.jpeg',
                    'filename' => 'multione-ez-series-overview.jpg',
                    'alt_text' => 'MultiOne EZ Series full-electric articulated loader',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-EZ-series-with-pallet-fork-1030x688-1.jpg',
                    'filename' => 'multione-ez-series-pallet-fork.jpg',
                    'alt_text' => 'MultiOne EZ Series electric loader lifting with pallet forks',
                ],
                [
                    'url' => 'https://www.multione.co.uk/wp-content/uploads/2022/01/MultiOne-mini-loader-EZ-series-with-sweeper-1030x688-1.jpg',
                    'filename' => 'multione-ez-series-sweeper.jpg',
                    'alt_text' => 'MultiOne EZ Series operating rotary collector sweeper',
                ],
            ],
        ],
    ],
];
