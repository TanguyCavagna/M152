/**
 * ShadowDOM pour les posts. Cela permet de gérer plus facielement le comportement des posts et évite la surcharge d'infos dans le HTML
 */
class Post extends HTMLElement {
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
                    padding: 0;
                    width: 100%;
                    justify-content: center;
                }
                .media + .media { margin-top: 10px; }
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
                .edit svg path {
                    color: #3b5999;
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
        const media_list = medias.split(',');
        const type_list = types.split(',');
        let result = '';

        for (let i = 0; i < media_list.length; i++) {
            let type = type_list[i];
            let media = media_list[i];
            result += '<div class="media">';
            
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

        return result;
    }
}

try {
    customElements.define('my-post', Post);
} catch (e) {
    if (e instanceof DOMException) {
        console.error('DOMException : ', e);
    } else {
        throw e;
    }
}