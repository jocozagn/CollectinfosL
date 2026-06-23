document.addEventListener('DOMContentLoaded', function () {
    initAdminSidebar();
    initFileUploads();
});

function initAdminSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    const toggle = document.getElementById('admin-sidebar-toggle');
    const overlay = document.getElementById('admin-sidebar-overlay');

    if (!sidebar || !toggle) {
        return;
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        if (overlay) {
            overlay.classList.remove('open');
        }
        toggle.setAttribute('aria-expanded', 'false');
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.add('fa-bars');
            icon.classList.remove('fa-xmark');
        }
    }

    function openSidebar() {
        sidebar.classList.add('open');
        if (overlay) {
            overlay.classList.add('open');
        }
        toggle.setAttribute('aria-expanded', 'true');
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        }
    }

    toggle.addEventListener('click', function () {
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    sidebar.querySelectorAll('.admin-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            closeSidebar();
        }
    });
}

function initFileUploads() {
    document.querySelectorAll('.form-group input[type="file"]').forEach(function (input) {
        if (input.closest('.file-upload')) {
            return;
        }

        const acceptHint = fileAcceptHint(input.accept);
        const wrapper = document.createElement('div');
        wrapper.className = 'file-upload';

        const zone = document.createElement('div');
        zone.className = 'file-upload__zone';
        zone.innerHTML =
            '<i class="fa-solid fa-cloud-arrow-up file-upload__icon" aria-hidden="true"></i>' +
            '<img class="file-upload__preview" alt="" hidden>' +
            '<p class="file-upload__prompt">Glisser-déposer ou <strong>parcourir</strong></p>' +
            '<p class="file-upload__name"></p>' +
            (acceptHint ? '<p class="file-upload__hint">' + acceptHint + '</p>' : '');

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(zone);
        wrapper.appendChild(input);

        const nameEl = zone.querySelector('.file-upload__name');
        const previewEl = zone.querySelector('.file-upload__preview');
        const isImageField = (input.accept || '').includes('image');

        function syncFileState() {
            const file = input.files && input.files[0];

            if (!file) {
                wrapper.classList.remove('has-file', 'is-image');
                nameEl.textContent = '';
                previewEl.hidden = true;
                previewEl.removeAttribute('src');
                return;
            }

            wrapper.classList.add('has-file');
            nameEl.textContent = file.name;

            if (isImageField && file.type.startsWith('image/')) {
                wrapper.classList.add('is-image');
                previewEl.src = URL.createObjectURL(file);
                previewEl.hidden = false;
            } else {
                wrapper.classList.remove('is-image');
                previewEl.hidden = true;
                previewEl.removeAttribute('src');
            }
        }

        input.addEventListener('change', syncFileState);

        ['dragenter', 'dragover'].forEach(function (eventName) {
            wrapper.addEventListener(eventName, function (event) {
                event.preventDefault();
                wrapper.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            wrapper.addEventListener(eventName, function (event) {
                event.preventDefault();
                wrapper.classList.remove('is-dragover');
            });
        });

        wrapper.addEventListener('drop', function (event) {
            const files = event.dataTransfer && event.dataTransfer.files;
            if (!files || !files.length) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(files[0]);
            input.files = transfer.files;
            syncFileState();
        });
    });
}

function fileAcceptHint(accept) {
    if (!accept) {
        return 'Tous types de fichiers';
    }

    if (accept.includes('image')) {
        return 'PNG, JPG, WebP, GIF…';
    }

    if (accept.includes('video') || accept.includes('audio')) {
        return 'Vidéo ou audio';
    }

    return accept.replace(/\*/g, '').replace(/,/g, ', ').trim();
}
