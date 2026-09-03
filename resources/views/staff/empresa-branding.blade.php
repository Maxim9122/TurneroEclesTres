<x-layouts.app title="Marca de mi empresa - EclesTres" :logout-route="route('staff.logout')">

    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Logo y fondo de tu negocio</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2">

        {{-- Formulario --}}
        <form method="POST" action="{{ route('staff.empresa.branding.update') }}" enctype="multipart/form-data"
            class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-5"
            x-data="{
                logoPreview: '{{ $empresa->logo_path ? asset('storage/'.$empresa->logo_path) : '' }}',
                fondoPreview: '{{ $empresa->imagen_fondo_path ? asset('storage/'.$empresa->imagen_fondo_path) : '' }}',
                color: '{{ $empresa->color_fondo ?: '#EDE7DD' }}',
            }">
            @csrf

            <div>
                <label class="block text-sm mb-1">Logo</label>
                <input type="file" name="logo" accept="image/*"
                    @change="logoPreview = URL.createObjectURL($event.target.files[0])"
                    class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
                <p class="text-xs text-mate-tinta/50 mt-1">JPG, PNG, WEBP o SVG. Máximo 2MB.</p>
            </div>

            <div>
                <label class="block text-sm mb-1">Color de fondo</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color_fondo" x-model="color"
                        class="h-10 w-16 rounded border border-mate-borde cursor-pointer">
                    <span class="text-sm text-mate-tinta/60" x-text="color"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm mb-1">Imagen de fondo (opcional)</label>
                <input type="file" name="imagen_fondo" accept="image/*"
                    @change="fondoPreview = URL.createObjectURL($event.target.files[0])"
                    class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
                <p class="text-xs text-mate-tinta/50 mt-1">
                    Si cargás una imagen, tiene prioridad sobre el color. Máximo 5MB.
                </p>
            </div>

            @if ($empresa->imagen_fondo_path)
                <label class="flex items-center gap-2 text-sm text-mate-tinta/70">
                    <input type="checkbox" name="quitar_imagen_fondo" value="1" class="rounded border-mate-borde"
                        @change="fondoPreview = $event.target.checked ? '' : fondoPreview">
                    Quitar imagen de fondo actual y usar el color sólido
                </label>
            @endif

            <button type="submit"
                class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
                Guardar cambios
            </button>
        </form>

        {{-- Preview --}}
        <div x-data="{
                logoPreview: '{{ $empresa->logo_path ? asset('storage/'.$empresa->logo_path) : '' }}',
                fondoPreview: '{{ $empresa->imagen_fondo_path ? asset('storage/'.$empresa->imagen_fondo_path) : '' }}',
                color: '{{ $empresa->color_fondo ?: '#EDE7DD' }}',
            }">
            <p class="text-sm text-mate-tinta/60 mb-2">Así se va a ver tu perfil público:</p>
            <div class="rounded-lg border border-mate-borde overflow-hidden aspect-[9/14] max-w-xs mx-auto sm:mx-0"
                :style="fondoPreview ? `background-image:url(${fondoPreview}); background-size:cover; background-position:center;` : `background-color:${color};`">
                <div class="h-full w-full bg-black/10 flex flex-col items-center justify-center gap-3 p-4">
                    <img x-show="logoPreview" :src="logoPreview" class="w-16 h-16 rounded-full object-cover bg-white shadow">
                    <span class="text-white font-display text-lg drop-shadow">{{ $empresa->nombre }}</span>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>