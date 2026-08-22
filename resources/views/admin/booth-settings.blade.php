<x-admin-layout title="Booth Settings">
    <div class="grid gap-8 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.booth-settings.update') }}" enctype="multipart/form-data" class="space-y-6 lg:col-span-2">
            @csrf
            @method('PUT')

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Booth Template</h2>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Transparent Booth PNG</label>
                    <input type="file" name="booth_template" accept="image/png" id="template-input" class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Transparent PNG. If left empty, a placeholder graphic is used.</p>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Logo Position</h2>
                <p class="mt-1 text-xs text-gray-400">Move the logo anywhere across the booth. All values are percentages of the booth frame.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Horizontal Position</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="logo-x-value">{{ $settings['booth_logo_x'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_logo_x" id="logo-x" min="0" max="100" step="0.5" value="{{ old('booth_logo_x', $settings['booth_logo_x']) }}" class="mt-1 w-full">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Vertical Position</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="logo-y-value">{{ $settings['booth_logo_y'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_logo_y" id="logo-y" min="0" max="100" step="0.5" value="{{ old('booth_logo_y', $settings['booth_logo_y']) }}" class="mt-1 w-full">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Logo Width</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="logo-width-value">{{ $settings['booth_logo_width'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_logo_width" id="logo-width" min="20" max="60" step="0.5" value="{{ old('booth_logo_width', $settings['booth_logo_width']) }}" class="mt-1 w-full">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Logo Max Height</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="logo-height-value">{{ $settings['booth_logo_max_height'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_logo_max_height" id="logo-height" min="6" max="18" step="0.5" value="{{ old('booth_logo_max_height', $settings['booth_logo_max_height']) }}" class="mt-1 w-full">
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold text-gray-900">School Name</h2>
                <p class="mt-1 text-xs text-gray-400">Move the school name anywhere across the booth and control how strongly it curves.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Horizontal Position</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="name-x-value">{{ $settings['booth_name_x'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_name_x" id="name-x" min="0" max="100" step="0.5" value="{{ old('booth_name_x', $settings['booth_name_x']) }}" class="mt-1 w-full">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Vertical Position</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="name-y-value">{{ $settings['booth_name_y'] }}</span>%</span>
                        </div>
                        <input type="range" name="booth_name_y" id="name-y" min="0" max="100" step="0.5" value="{{ old('booth_name_y', $settings['booth_name_y']) }}" class="mt-1 w-full">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Curve</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="name-curve-value">{{ $settings['booth_name_curve'] }}</span></span>
                        </div>
                        <input type="range" name="booth_name_curve" id="name-curve" min="0" max="160" step="5" value="{{ old('booth_name_curve', $settings['booth_name_curve']) }}" class="mt-1 w-full">
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Booth Grid</h2>
                <p class="mt-1 text-xs text-gray-400">Controls the public School Booths grid layout.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Booths per Row</label>
                        <select name="booth_grid_columns" class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ([1, 2, 3, 4] as $n)
                                <option value="{{ $n }}" @selected((int) old('booth_grid_columns', $settings['booth_grid_columns']) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Space Between Booths</label>
                            <span class="text-sm font-semibold text-indigo-600"><span id="grid-gap-value">{{ $settings['booth_grid_gap'] }}</span>rem</span>
                        </div>
                        <input type="range" name="booth_grid_gap" id="grid-gap" min="0" max="20" step="0.5" value="{{ old('booth_grid_gap', $settings['booth_grid_gap']) }}" class="mt-1 w-full">
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Booth Modal</h2>
                <p class="mt-1 text-xs text-gray-400">Controls how transparent the school booth popup panel is on the public site.</p>
                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Background Opacity</label>
                        <span class="text-sm font-semibold text-indigo-600"><span id="modal-opacity-value">{{ $settings['booth_modal_opacity'] }}</span>%</span>
                    </div>
                    <input type="range" name="booth_modal_opacity" id="modal-opacity" min="10" max="100" step="5" value="{{ old('booth_modal_opacity', $settings['booth_modal_opacity']) }}" class="mt-1 w-full">
                </div>
            </div>

            <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-500">Save Booth Settings</button>
        </form>

        <div>
            <div class="sticky top-6">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Live Preview</h2>
                <div class="mx-auto max-w-xs">
                    <div class="booth-frame" id="preview-frame">
                        @if (! empty($settings['booth_template_path']))
                            <img id="preview-template" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings['booth_template_path']) }}" class="booth-template-img" alt="">
                        @else
                            <x-booth-placeholder-svg class="booth-template-svg" id="preview-template-svg" />
                        @endif
                        <div class="booth-logo-placeholder" id="preview-logo"
                            style="top:{{ $settings['booth_logo_y'] }}%; left:{{ $settings['booth_logo_x'] }}%; width:{{ $settings['booth_logo_width'] }}%; height:{{ $settings['booth_logo_max_height'] }}%;">
                            LOGO
                        </div>
                        <x-booth-name-svg name="Sample School" path-id="admin-preview-name-path" :curve="$settings['booth_name_curve']"
                            :x="$settings['booth_name_x']" :y="$settings['booth_name_y']" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const previewLogo = document.getElementById('preview-logo');

        function bindSlider(id, valueId, styleProp) {
            const input = document.getElementById(id);
            const valueLabel = document.getElementById(valueId);
            input?.addEventListener('input', (e) => {
                valueLabel.textContent = e.target.value;
                previewLogo.style[styleProp] = e.target.value + '%';
            });
        }

        bindSlider('logo-x', 'logo-x-value', 'left');
        bindSlider('logo-y', 'logo-y-value', 'top');
        bindSlider('logo-width', 'logo-width-value', 'width');
        bindSlider('logo-height', 'logo-height-value', 'height');

        function updateNamePath() {
            const x = parseFloat(document.getElementById('name-x').value);
            const y = parseFloat(document.getElementById('name-y').value);
            const curve = parseFloat(document.getElementById('name-curve').value);

            const centerX = (x / 100) * 1089;
            const centerY = (y / 100) * 1444;
            const halfWidth = 404.5;
            const startX = centerX - halfWidth;
            const endX = centerX + halfWidth;
            const half = curve / 2;
            const endY = centerY + half;
            const controlY = centerY - half;

            const path = document.getElementById('admin-preview-name-path');
            path.setAttribute('d', `M ${startX} ${endY} Q ${centerX} ${controlY} ${endX} ${endY}`);
            window.fitBoothCurvedText(path.closest('svg'));
        }

        document.getElementById('name-x')?.addEventListener('input', (e) => {
            document.getElementById('name-x-value').textContent = e.target.value;
            updateNamePath();
        });

        document.getElementById('name-y')?.addEventListener('input', (e) => {
            document.getElementById('name-y-value').textContent = e.target.value;
            updateNamePath();
        });

        document.getElementById('name-curve')?.addEventListener('input', (e) => {
            document.getElementById('name-curve-value').textContent = e.target.value;
            updateNamePath();
        });

        document.getElementById('grid-gap')?.addEventListener('input', (e) => {
            document.getElementById('grid-gap-value').textContent = e.target.value;
        });

        document.getElementById('modal-opacity')?.addEventListener('input', (e) => {
            document.getElementById('modal-opacity-value').textContent = e.target.value;
        });

        document.getElementById('template-input')?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (! file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                let img = document.getElementById('preview-template');
                if (! img) {
                    const old = document.getElementById('preview-template-svg');
                    img = document.createElement('img');
                    img.id = 'preview-template';
                    img.className = 'booth-template-img';
                    old.replaceWith(img);
                }
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
</x-admin-layout>
