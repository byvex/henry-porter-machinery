@if(session('success'))<div role="status" class="rounded-xl border border-green-300 bg-green-50 p-4 text-sm text-green-900">{{ session('success') }}</div>@endif
@if($errors->any())
<div role="alert" class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900">
    <p class="font-semibold">Please check the following:</p>
    <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
</div>
@endif
