<x-school-layout title="Gallery">
    <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
        <h2 class="text-base font-semibold text-gray-900">Upload Image</h2>
        <form method="POST" action="{{ route('school.gallery.store') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-4">
            @csrf
            <input type="file" name="image" accept="image/*" required class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button class="rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Upload</button>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        @forelse ($images as $image)
            <div class="group relative aspect-square overflow-hidden rounded-xl bg-gray-100">
                <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover">
                <form method="POST" action="{{ route('school.gallery.destroy', $image) }}" onsubmit="return confirm('Remove this image?')"
                      class="absolute right-2 top-2 opacity-0 transition group-hover:opacity-100">
                    @csrf @method('DELETE')
                    <button class="rounded-full bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow">Remove</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">No gallery images uploaded yet.</p>
        @endforelse
    </div>
</x-school-layout>
