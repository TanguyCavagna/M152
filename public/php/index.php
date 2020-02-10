<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\PostController;

$postController = new PostController();

$posts = $postController->GetAll();
?>
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
    <script defer>
        const postsDOM = document.querySelector('.posts');
        
        $(document).ready(() => {

            fetch('./getAll.php')
            .then(response => {
                return response.json();
            })
            .then(json => {
                const posts = json.posts;

                posts.forEach(post => {
                    $(postsDOM).append(`<my-post comment="${post.commentary}" ${post.medias != null ? 'medias="' + post.medias + '"': ''} ${post.types != null ? 'types="' + post.types + '"': ''} id="${post.idPost}"></my-post>`);
                });
            })
            .catch(err => {
                console.error(err);
            })
        });
    </script>
</body>

</html>