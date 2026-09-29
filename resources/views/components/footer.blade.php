<footer class="text-center py-6 px-4 text-xs text-mate-tinta/50 border-t border-mate-borde mt-10">
    <p>EclesTres © {{ now()->year }}</p>
    <p class="mt-1">
        @php($emailContacto = \App\Support\Contacto::email())
        Desarrollado por LunaSoft ·
        @if ($emailContacto)
            <a href="mailto:{{ $emailContacto }}" class="underline">{{ $emailContacto }}</a> ·
        @endif
        Tel: {{ \App\Support\Contacto::telefono() }}
    </p>
    <p class="mt-2">
        <a href="{{ route('staff.login') }}" class="underline text-mate-tinta/40">Acceso para negocios registrados</a>
    </p>
</footer>