<div style="display: flex; align-items: center; gap: 0.75rem;">
    <img
        src="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}"
        alt="Rumitech"
        style="height: 2.5rem; width: 2.5rem; flex-shrink: 0; border-radius: 1rem; object-fit: cover; border: 1px solid #dbeafe; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2), 0 4px 6px -4px rgba(37, 99, 235, 0.2);"
    />

    <span style="display: flex; min-width: 0; flex-direction: column;">
        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.875rem; font-weight: 600; letter-spacing: -0.025em; color: #0f172a;">
            Rumitech
        </span>
        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.625rem; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: #3b82f6;">
            Content manager
        </span>
    </span>
</div>
