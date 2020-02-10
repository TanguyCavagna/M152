/**
 * ShadowDOM pour les posts. Cela permet de gérer plus facielement le comportement des posts et évite la surcharge d'infos dans le HTML
 */
class PostModule extends HTMLElement {
    constructor() {
        super();
        const id = this.getAttribute('id') || -1;
        const comment = this.getAttribute('comment') || 'Post sans commentaire';
        const medias = this.getAttribute('medias') || '';
        const types = this.getAttribute('types') || '';
        const root = this.attachShadow({mode: 'open'});
        root.innerHTML = `<div>
            ${this.buildStyle()}
            ${this.buildPost(id, comment, medias, types)}
        </div>`;
    }

    /**
     * Mise en places des différents events
     */
    connectedCallback() {
        const post = this.shadowRoot.querySelector('.post');
        const comment = this.shadowRoot.querySelector('.post-comment');
        const textarea = this.shadowRoot.querySelector('textarea');
        const deleteMediaBtns = this.shadowRoot.querySelectorAll('.delete-media');
        const deleteBtn = this.shadowRoot.querySelector('.delete');
        const editBtn = this.shadowRoot.querySelector('.edit');
        const validBtn = this.shadowRoot.querySelector('.valid');

        // Supprime le poste
        deleteBtn.addEventListener('click', e => {
            const id = post.dataset.id;

            let formData = new FormData();
            formData.append('id', id);

            const init = {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            };

            fetch('./deletePost.php', init)
            .then(response => {
                if (response.status === 200) {
                    $(postsDOM).empty(); // Variable globale à la page index

                    fetch('./getAll.php')
                    .then(response => {
                        return response.json();
                    })
                    .then(json => {
                        const posts = json.posts;

                        posts.forEach(post => {
                            let mediaNames = '';
                            let mediaTypes = '';

                            // Création d'une string concaténant les noms et types des médias
                            if (post.medias !== null) {
                                post.medias.forEach(media => {
                                    mediaNames += `${media.name},`;
                                    mediaTypes += `${media.type},`;
                                });
                            }

                            // Suppression de la dernière virgule
                            mediaNames = mediaNames.substring(0, mediaNames.length - 1);
                            mediaTypes = mediaTypes.substring(0, mediaTypes.length - 1);

                            $(postsDOM).append(`<my-post comment="${post.comment}" ${mediaNames != '' ? 'medias="' + mediaNames + '"': ''} ${mediaTypes != '' ? 'types="' + mediaTypes + '"': ''} id="${post.id}"></my-post>`);
                        });
                    });
                } else if (response.status === 422) { // Id du poste vide
                    console.log("Id du poste vide");
                } else if (response.status === 415) { // Id du poste non entier
                    console.log("Id du poste non entier");
                } else if (response.status !== 0) {
                    console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
                } else {
                    console.log('Erreur inconnue. Veuillez réessayer.');
                }
            })
            .catch(err => {
                console.error(err);
            });
        });

        // Active l'edit du poste
        editBtn.addEventListener('click', e => {
            comment.classList.add('hide');
            textarea.classList.remove('hide');
            editBtn.classList.add('hide');
            validBtn.classList.remove('hide');

            for (let index = 0; index < deleteMediaBtns.length; index++) {
                const element = deleteMediaBtns[index];
                element.classList.remove('hide');
            }
        });

        // Change le commantaire du posts
        validBtn.addEventListener('click', e => {
            const id = post.dataset.id;

            let formData = new FormData();
            formData.append('id', id);
            formData.append('comment', textarea.value);

            const init = {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            };

            fetch('./updateComment.php', init)
            .then(response => {
                if (response.status === 200) {
                    fetch('./getAll.php')
                    .then(response => {
                        return response.json();
                    })
                    .then(json => {
                        if (response.status === 200) {
                            comment.innerHTML = textarea.value;

                            comment.classList.remove('hide');
                            textarea.classList.add('hide');
                            editBtn.classList.remove('hide');
                            validBtn.classList.add('hide');
                
                            for (let index = 0; index < deleteMediaBtns.length; index++) {
                                const element = deleteMediaBtns[index];
                                element.classList.add('hide');
                            }
                        } else if (response.status !== 0) {
                            console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
                        } else {
                            console.log('Erreur inconnue. Veuillez réessayer.');
                        }
                    });
                } else if (response.status === 422) { // Commentaire vide
                    console.log("Commentaire vide");
                } else if (response.status !== 0) {
                    console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
                } else {
                    console.log('Erreur inconnue. Veuillez réessayer.');
                }
            })
            .catch(err => {
                console.error(err);
            });
        });

        // Supprime un media
        for (let index = 0; index < deleteMediaBtns.length; index++) {
            const element = deleteMediaBtns[index];
            const mediaContainer = element.closest('.media');

            element.addEventListener('click', e => {
                const mediaName = mediaContainer.dataset.mediaName;

                let formData = new FormData();
                formData.append('mediaName', mediaName);

                const init = {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                };

                fetch('./deleteMedia.php', init)
                .then(response => {
                    if (response.status === 200) {
                        fetch('./getAll.php')
                        .then(response => {
                            return response.json();
                        })
                        .then(json => {
                            mediaContainer.parentNode.removeChild(mediaContainer);
                        });
                    } else if (response.status === 422) { // L'id du media est vide
                        console.log("L'id du media est vide");
                    } else if (response.status !== 0) {
                        console.log(`Erreur HTTP ${xhr.status}. Veuillez réessayer.`);
                    } else {
                        console.log('Erreur inconnue. Veuillez réessayer.');
                    }
                })
                .catch(err => {
                    console.error(err);
                });
            });
        }
    }

    /**
     * Construit le style de notre post
     * @returns {string}
     */
    buildStyle() {
        return `
            <style>
                :host {
                    display: block;
                }
                .post {
                    background: #fff;
                    border-radius: 0;
                }
                .post + .post {
                    margin-top: 30px;
                }
                .post { border-radius: 0; }
                .medias {
                    padding: 0;
                }
                .media {
                    position: relative;
                    padding: 0;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                .media + .media { margin-top: 10px; }
                .delete-media {
                    position: absolute;
                    top: 5px;
                    right: 5px;
                    z-index: 3;
                }
                .delete-media path {
                    color: black;
                }
                .delete-media:hover {
                    cursor: pointer;
                }
                .delete-media:hover path {
                    color: #E14E4E;
                }
                img {
                    object-fit: cover;
                    width: 100%;
                }
                audio {
                    width: 95%;
                    border-radius: 15px;
                }
                .big-title {
                    font-size: 2rem;
                    font-family: 'Gotham ultra';
                }
                .post-comment {
                    margin: 0;
                    font-size: 1.7rem;
                    font-family: 'Gotham light';
                }
                textarea {
                    width: 100%;
                    height: 100px;
                    border: solid 1px black;
                    font-family: 'Gotham light';
                    resize: none;
                }
                .card-footer {
                    display: flex;
                    justify-content: right;
                    padding: 5px 0;
                }
                span {
                    margin-right: 20px;
                }
                span:hover {
                    cursor: pointer;
                }
                .delete svg path {
                    color: #E14E4E;
                }
                .edit svg path,
                .valid svg path {
                    color: #3b5999;
                }
                .hide {
                    display: none;
                }
            </style>`;
    }

    /**
     * Construit le post
     * @param {sring} commentary Titre du post
     * @param {string} media Nom des medias
     * @param {string} type Mime type des medias
     * @returns {string}
     */
    buildPost(id, commentary, medias, types) {
        return `
        <div class="card post" data-id="${id}">
            <div class="medias">
                ${this.buildMedias(medias, types)}
            </div>
            <div class="card-body">
                <p class="post-comment">${commentary}</p>
                <textarea class="hide">${commentary}</textarea>
            </div>

            <div class="card-footer">
                <span class="delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M3 6v18h18v-18h-18zm5 14c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm5 0c0 .552-.448 1-1 1s-1-.448-1-1v-10c0-.552.448-1 1-1s1 .448 1 1v10zm4-18v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.315c0 .901.73 2 1.631 2h5.712z"/>
                    </svg>
                </span>
                <span class="edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M19.769 9.923l-12.642 12.639-7.127 1.438 1.438-7.128 12.641-12.64 5.69 5.691zm1.414-1.414l2.817-2.82-5.691-5.689-2.816 2.817 5.69 5.692z"/>
                    </svg>
                </span>
                <span class="valid hide">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/>
                    </svg>
                </span>
            </div>
        </div>
        `;
    }

    /**
     * Construit les medias
     * @param {string} medias Nom de touts les medias
     * @returns {string}
     */
    buildMedias(medias, types) {
        let result = '';
        if (medias != '' && types != '') {
            const media_list = medias.split(',');
            const type_list = types.split(',');

            for (let i = 0; i < media_list.length; i++) {
                let type = type_list[i];
                let media = media_list[i];
                result += `<div class="media" data-media-name="${media}">
                    <svg class="delete-media hide" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/>
                    </svg>`;
                
                if (type.includes('image')) {
                    result += `<img src="../uploads/${media}" alt="" class="card-top">`;
                } else if (type.includes('audio')) {
                    result += `<audio controls>
                        <source src="../uploads/${media}" type="${type}">
                    </audio>`;
                } else if (type.includes('video')) {
                    result += `<video loop autoplay muted>
                        <source src="../uploads/${media}" type="${type}">
                    </video>`;
                }
                
                result += '</div>';
            };
        }

        return result;
    }
}

try {
    customElements.define('my-post', PostModule);
} catch (e) {
    if (e instanceof DOMException) {
        console.error('DOMException : ', e);
    } else {
        throw e;
    }
}