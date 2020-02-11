/**
 * @filesource upload.js
 * @brief Permet de mettre en ligne les fichiers passé dans le forumlaire du poste.
 *        Le fichier de base est grandement inspiré de celui de Nicolas Etlin.
 * @author Nicloas Etlin
 * @author Tanguy Cavagna <tanguy.cvgn@eduge.ch>
 * @date 2020-02-11
 * @version 1.0.0
 */

/**
 * Pour ce fichier, j'ai du créer des fonctions dédiées pour la mise en place des différents
 * events, et ce à cause du fait que j'ai utiliser du shadow dom pour l'affichage de mes posts.
 *
 * Étant donné que dans du shadow dom nous avons pas accès à tout ce qui exterieur au shadow dom,
 * j'ai du faire en sorte que le script situé dans l'index.php puisse redéfinir mes variable de
 * formulaire, drop-zone et progress-bar. C'est pour cela que les fonctions de setup sont utiles.
 */

'use strict'; // To allow the use of undeclared variables

const files = [];
let filesFromLastBatch = [];
let form = document.querySelector('.post-form');
let dropZone = document.querySelector('.file-upload');
let progressBar = document.querySelector('.progress');

//==================================================================================================
// Fonctions
//==================================================================================================

/**
 * Ajout plusieurs fichier à l'uploader de fichier
 * @param {FileList} fileList Les fichiers à ajouter
 */
function addFiles(fileList) {
    // Création d'un promesse pour être sûre que tous les fichiers ont bien été pris en compte
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
                alert(`Le fichier "${file.name} n'est pas d'un type supporté."`);
                return;
            }

            const fileInfo = {
                data: file,
                name: file.name,
                type,
                dataUrl: null,
            };

            // Créer une url pour les données de l'image
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

    Promise.all(promises) // Attente avant que tout les fichiers ont bien été pris en compte ...
    .then((filesInfo) => {
        filesFromLastBatch = filesInfo;
        files.push(...filesInfo);

        showFiles();
    });
}

/**
 * Affiche les fichiers dans le forumlaire
 */
function showFiles() {
    let html = '';

    // Construit le HTML pour tout les éléments mis en ligne
    files.forEach((file, index) => {
        const indexInAnimation = filesFromLastBatch.indexOf(file);
        const hasAnimation = indexInAnimation !== -1;

        let tooltip = '';
        if (file.type === 'image') { // Prévisualisation de l'image
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

    // Met à jour la liste des fichiers
    form.querySelector('.uploaded-files').innerHTML = html;
}

/**
 * Setup les events sur les boutons d'ajout de fichiers.
 */
function setupFileButtons() {
    if (form !== null) {
        form.querySelectorAll('.open-file-dialog-buttons span').forEach(button => {
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
    } else {
        console.log('Le formulaire d\ajout de fichier est inexistant.');
        return;
    }
}

/**
 * Setup l'event de soumission de formulaire.
 */
function setupFormSubmission() {
    if (form !== null) {
        form.addEventListener('submit', e => {
            e.preventDefault();

            submitForm();
        });
    } else {
        console.log('Le formulaire d\ajout de fichier est inexistant.');
        return;
    }
}

/**
 * Setup le drag & drop de fichiers dans le formulaire
 */
function setupDragDropEvent() {
    form.addEventListener('dragover', e => {
        e.preventDefault();
        document.body.classList.add('is-dragging');

        if (dropZone.contains(e.target)) {
            dropZone.classList.add('file-upload-active');
        }
    });

    form.addEventListener('dragleave', e => {
        e.preventDefault();

        if (dropZone.contains(e.target) && !dropZone.contains(e.relatedTarget)) {
            dropZone.classList.remove('file-upload-active');
        }
    });

    form.addEventListener('dragexit', e => {
        e.preventDefault();
        document.body.classList.remove('is-dragging');
    });

    form.addEventListener('drop', e => {
        if (dropZone.contains(e.target)) {
            e.preventDefault();

            dropZone.classList.remove('file-upload-active');
            document.body.classList.remove('is-dragging');

            addFiles(e.dataTransfer.files);
        }
    });
}

/**
 * Soumet le forumlaire
 */
function submitForm(idPost = null) {
    if (form !== null) {
        // List all the form data
        const formData = new FormData(form);
        files.forEach(file => {
            formData.append('files[]', file.data);
        });
        formData.append('idPost', idPost);

        // Send the request
        const xhr = new XMLHttpRequest();
        xhr.open(form.method, form.action);

        // Show the progress bar
        if (progressBar !== null) {
            xhr.upload.addEventListener('progress', e => {
                if (e.lengthComputable) {
                    const progress = e.loaded / e.total;
                    progressBar.style.width = `${progress * 100}%`;
                }
            });
        }
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

                if (response.errors.length === 0) {
                    // Pas d'erreur : Création d'une alerte pour l'utilisateur et redirection uniquement si aucun post n'a été
                    //                donné en tant que parametre. Sinon, création d'un évènement personnalisé pour indiquer à
                    //                la page index.php de recharger les données en base.
                    const alerts = document.querySelector('.alerts');

                    let success = document.createElement('div');
                    success.classList.add('alert');
                    success.classList.add('alert-success');
                    success.setAttribute('role', 'alert');
                    success.innerHTML = idPost !== null ? 'Rechargement de la page dans 2s.' : 'Le poste à bien été mis en ligne. Redirection dans 2s.';
                    alerts.append(success);

                    // Affiche un timer avant la redirection dans l'alert
                    let seconds = 2;
                    let timer = setInterval(() => {
                        seconds--;
                        success.innerHTML = idPost !== null ? `Rechargement de la page dans ${seconds}s.` : `Le poste à bien été mis en ligne. Redirection dans ${seconds}s.`;
                    }, 1000);

                    // Redirection ou lancement d'évènement
                    setTimeout(() => {
                        clearInterval(timer);
                        alerts.removeChild(success);

                        if (idPost === null) {
                            window.location.href = 'index.php';
                        } else {
                            let readyToReload = new CustomEvent('readyToReload');
                            document.dispatchEvent(readyToReload);
                        }
                    }, seconds * 1000);
                } else {
                    // Des erreurs son apparues : Affichage de l'erreur en question à l'utilisateur
                    console.log(response.errors);

                    showError(response.errors);
                }
            } else if (xhr.status === 413) {
                showError(
                    'Les fichiers téléversés sont trop volumineux et sont refusés par le serveur. ' +
                    'Veuillez tenter d’envoyer moins de fichiers à la fois.'
                );
            } else if (xhr.status === 415) {
                showError('Un des types des médias mis en ligne n\'est pas pris en compte.');
            } else if (xhr.status === 422) {
                showError('Un élément du formulaire semble être vide.');
            } else if (xhr.status !== 0) {
                console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
            } else {
                console.log('Erreur inconnue. Veuillez réessayer.');
            }
        };

        xhr.send(formData);
    } else {
        console.log('Le formulaire d\ajout de fichier est inexistant.');
        return;
    }
}

/**
 * Show an alert with the error and log it in the console
 * @param {string} error Error message
 */
function showError(error) {
    const alerts = document.querySelector('.alerts');
    let danger = document.createElement('div');
    danger.classList.add('alert');
    danger.classList.add('alert-danger');
    danger.setAttribute('role', 'alert');
    danger.innerHTML = error;

    alerts.append(danger);

    let seconds = 5;
    setTimeout(() => {
        alerts.removeChild(danger);
    }, seconds * 1000);

    console.log('%c \\/ Une erreur est survenue \\/', 'font-weight: bold; color: red; font-size: 2rem;');
    console.log(error);
}

//==================================================================================================
// Mise en place des évènement
//==================================================================================================

/**
 * Met en place la soumission du formulaire
 */
if (form !== null) {
    setupFormSubmission();
}

/**
 * Met en place l'ouverture de sélection de fichier sur les boutons
 */
if (form !== null) {
    setupFileButtons();
}

/**
 * Met en place le drag & drop
 */
if (form !== null) {
    setupDragDropEvent();
}