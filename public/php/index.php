<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Facebook CFPT</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.min.css">
</head>

<body>
    <?php require_once '../includes/nav.php'; ?>

    <div class="alerts"></div>

    <main class="row">
        <!-- ============= START INFOS ============= -->
        <aside class="col-lg-3 col-md-5 col-sm-12">
            <div class="card infos">
                <img class="card-img-top" src="https://via.placeholder.com/500x500" alt="Card image cap">
                <div class="card-body">
                    <h5 class="card-title">Nom du blog</h5>
                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                </div>
            </div>
        </aside>
        <!-- ============= END INFOS ============= -->

        <!-- ============= START POSTS ============= -->
        <section class="col-lg-8 col-md-7 col-sm-12">
            <div class="card post">
                <div class="card-body">
                    <h5 class="card-title big-title">WELCOME</h5>
                </div>
            </div>

            <div class="posts"></div>
        </section>
        <!-- ============= END POSTS ============= -->
    </main>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="../js/search.js" defer></script>
    <script src="../js/post.js" type="module" defer></script>
    <script src="../js/upload.js" defer></script>
    <script defer>
        const postsDOM = document.querySelector('.posts');

        //==================================================================================================
        // Fonctions
        //==================================================================================================

        /**
         * Ajout le formulaire d'ajout de media dans le poste donner via son id
         */
        function createEditForm(postId) {
            const newPost = document.querySelector(`.post[data-id="${postId}"]`);

            $(newPost).prepend(`
            <form class="post-form hide" action="./addMediasInPost.php" method="POST" enctype="multipart/form-data">
                <div class="form-group file-upload">
                    <div class="file-upload-infos">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M7 11v13h2v-5h2v3h2v-7h2v9h2v-13h6l-11-11-11 11z"/></svg>
                        <h2>Ajouter des fichiers</h2>
                        <p>Vous pouvez ajouter un fichier en le glissant dans cette zone ou en utilisant les boutons.</p>
                    </div>
                    <ul class="uploaded-files"></ul>
                </div>
                <div class="form-group add-post__options">
                    <span class="add-post__options-inserts open-file-dialog-buttons">
                        <span class="inserts-element add-post__image" data-accept="image/*"></span>
                        <span class="inserts-element add-post__video" data-accept="video/*"></span>
                        <span class="inserts-element add-post__audio" data-accept="audio/*"></span>
                    </span>
                </div>
            </form>
            `);

            const myPost = newPost.querySelector('my-post');
            const editBtn = myPost.shadowRoot.querySelector('.edit');
            const validBtn = myPost.shadowRoot.querySelector('.valid');

            editBtn.addEventListener('click', e => {
                newPost.querySelector('.post-form').classList.remove('hide'); // Affiche le formulaire

                // Ré initialisation des variables nécessaire au script d'upload de fichiers
                form = document.querySelector('.post-form:not(.hide)');
                dropZone = form.querySelector('.file-upload');
                progressBar = form.querySelector('.progress');

                // Remise en place des évènements du formulaire
                setupFileButtons();
                setupFormSubmission();
                setupDragDropEvent();
            });

            validBtn.addEventListener('click', e => {
                submitForm(postId);

                newPost.querySelector('.post-form').classList.add('hide'); // Cache le formulaire
            });
        }

        /**
         * Affiche les postes
         *
         * @return void
         */
        function showPosts() {
            fetch('./getAll.php')
            .then(response => {
                return response.json();
            })
            .then(json => {
                const posts = json.posts;

                postsDOM.innerHTML = '';

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

                    $(postsDOM).append(`
                        <div class="post" data-id="${post.id}">
                            <my-post comment="${post.comment}" ${mediaNames != '' ? 'medias="' + mediaNames + '"': ''} ${mediaTypes != '' ? 'types="' + mediaTypes + '"': ''} id="${post.id}"></my-post>
                        </div>`);
                    
                    createEditForm(post.id);
                });
            })
            .catch(err => {
                console.error(err);
            })
        }

        //==================================================================================================
        // Document prêt
        //==================================================================================================

        $(document).ready(() => {
            showPosts();

            // Event custom créer dans le fichier upload.js
            document.addEventListener('readyToReload', () => {
                showPosts();
            });
        });
    </script>
</body>

</html>