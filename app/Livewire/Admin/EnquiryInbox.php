<?php

namespace App\Livewire\Admin;

use App\Models\Enquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EnquiryInbox extends Component
{
    use WithPagination;

    #[Locked]
    public ?string $id = null;

    public string $search = '';

    public string $filter = '';

    public string $status = 'new';

    public function boot(): void
    {
        Gate::authorize('manage-catalogue');
    }

    public function mount(?string $id = null): void
    {
        $this->id = $id;
        if ($id) {
            $this->status = Enquiry::findOrFail($id)->status;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        Gate::authorize('manage-catalogue');
        $this->validate(['status' => 'required|in:new,read,closed']);
        Enquiry::findOrFail($this->id)->update(['status' => $this->status]);
        session()->flash('success', 'Enquiry status updated.');
    }

    public function render(): View
    {
        $query = Enquiry::query();
        if ($this->filter !== '') {
            $query->where('status', $this->filter);
        }
        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%')->orWhere('product_name', 'like', '%'.$this->search.'%'));
        }

        return view('livewire.admin.enquiry-inbox', ['enquiry' => $this->id ? Enquiry::findOrFail($this->id) : null, 'enquiries' => $query->latest()->paginate(20)])->title('Enquiries');
    }
}
