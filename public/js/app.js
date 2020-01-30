'use strict'; // To allow the use of undeclared variables

const files = [];
let filesFromLastBatch = [];
const form = document.querySelector('.post-form');
const dropZone = document.querySelector('.file-upload');
const progressBar = document.querySelector('.progress');

/**
 * Add multiple files to the files uploader
 * @param {FileList} fileList The files to add
 */
function addFiles(fileList) {
    const promises = Array.from(fileList).map((file) => {
        return new Promise((resolve) => {
            let type;

            if (file.type.startsWith('image/')) {
                type = 'image';
            } else if (file.type.startsWith('video/')) {
                type = 'video';
            } else if (file.type.startsWith('audio/')) {
                type = 'audio';
            } else {
                alert(`Le fichier "${file.name} n'est pas d'un tpye supporté."`);
                return;
            }

            const fileInfo = {
                data: file,
                name: file.name,
                type,
                dataUrl: null,
            };

            // Generate a data URI for the file
            if (type == 'image') {
                const fileReader = new FileReader();

                fileReader.onload = () => {
                    fileInfo.dataUrl = fileReader.result;
                    resolve(fileInfo);
                };
                fileReader.readAsDataURL(file);
            } else {
                resolve(fileInfo);
            }
        });
    });

    Promise.all(promises) // Wait for all files to be processed...
    .then((filesInfo) => {
        filesFromLastBatch = filesInfo;
        files.push(...filesInfo);

        showFiles();
    });
}

/**
 * Show the files to upload on the screen
 */
function showFiles() {
    let html = '';

    // Build the HTML for each list element
    files.forEach((file, index) => {
        const indexInAnimation = filesFromLastBatch.indexOf(file);
        const hasAnimation = indexInAnimation !== -1;

        let tooltip = '';
        if (file.type === 'image') {
            tooltip = `
            <div class="file-preview-tooltip">
                <div class="file-preview-tooltip-arrow"></div>
                <img src="${file.dataUrl}" alt="${file.name}">
            </div>`
        }

        html += `
        <li class="uploaded-file file-type-${file.type} ${hasAnimation ? 'uploaded-file-new' : ''}">
            <div class="uploaded-file-icon" role="presentation" title="Fichier de type ${file.type}"></div>
            ${tooltip}

            <span>${file.name}</span>
            <button data-i="${index}" type="button" title="Retirer ce fichier">
                &times;
            </button>
        </li>
        `;
    });

    // Update the file list on screen
    document.querySelector('.uploaded-files').innerHTML = html;

    // Delete file button
    document.querySelectorAll('.uploaded-files button').forEach(button => {
        button.addEventListener('click', () => {
            const fileIndex = parseInt(button.getAttribute('data-i'), 10);

            files.splice(fileIndex, 1);
            filesFromLastBatch = [];

            showFiles();
        });
    });
}

/**
 * Manages the form submission
 */
form.addEventListener('submit', e => {
    e.preventDefault();

    // List all the form data
    const formData = new FormData(form);
    files.forEach(file => {
        formData.append('files[]', file.data);
    });

    // Send the request
    const xhr = new XMLHttpRequest();
    xhr.open(form.method, form.action);

    // Show the progress bar
    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const progress = e.loaded / e.total;
            progressBar.style.width = `${progress * 100}%`;
        }
    });
    xhr.onload = () => {
        form.classList.add('is-uploading');
    };
    xhr.onloadend = () => {
        form.classList.remove('is-uploading');
    };

    // Handle the request completion
    xhr.onreadystatechange = () => {
        if (xhr.readyState !== XMLHttpRequest.DONE) {
            return;
        }

        if (xhr.status === 200) { // OK
            const response = JSON.parse(xhr.responseText);

            if (response.errors.lenght === 0) {
                // No errors: redirect to the home page
                window.location.href = 'index.php';
            } else {
                // Otherwise: Show the errors
                console.log(response.errors);
            }
        } else if (xhr.status === 413) {
            console.log(
                'Les fichiers téléversés sont trop volumineux et sont refusés par le serveur. ' +
                'Veuillez tenter d’envoyer moins de fichiers à la fois.'
            );
        } else if (xhr.status === 415) {
            console.log('Un des types des médias mis en ligne n\'est pas pris en compte.');
        } else if (xhr.status === 422) {
            console.log('Un élément du formulaire semble être vide.');
        } else if (xhr.status !== 0) {
            console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
        } else {
            console.log('Erreur inconnue. Veuillez réessayer.');
        }
    };

    xhr.send(formData);
});

/**
 * Manages the file buttons
 */
document.querySelectorAll('.open-file-dialog-buttons span').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = "file";
        input.accept = button.dataset.accept;
        input.multiple = true;
        input.click();

        // Submission
        input.onchange = () => {
            addFiles(input.files);
        };
    });
});

//==================================================================================================
// Manages the files drag & drop
//==================================================================================================

document.addEventListener('dragover', e => {
    e.preventDefault();
    document.body.classList.add('is-dragging');

    if (dropZone.contains(e.target)) {
        dropZone.classList.add('file-upload-active');
    }
});

document.addEventListener('dragleave', e => {
    e.preventDefault();

    if (dropZone.contains(e.target) && !dropZone.contains(e.relatedTarget)) {
        dropZone.classList.remove('file-upload-active');
    }
});

document.addEventListener('dragexit', e => {
    e.preventDefault();
    document.body.classList.remove('is-dragging');
});

document.addEventListener('drop', e => {
    if (dropZone.contains(e.target)) {
        e.preventDefault();

        dropZone.classList.remove('file-upload-active');
        document.body.classList.remove('is-dragging');

        addFiles(e.dataTransfer.files);
    }
});