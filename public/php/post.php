<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Facebook CFPT - Post</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.min.css">
</head>

<body>
    <?php require_once '../includes/nav.php'; ?>

    <main class="add-post row">
        <form class="col-md-4 post-form" action="./uploadPost.php" method="POST" enctype="multipart/form-data">
            <div class="form-group progress">
            </div>
            <div class="form-group add-post__body">
                <span class="add-post__user-img">
                    <img src="https://via.placeholder.com/100x100" alt="Card image cap">
                </span>
                <textarea class="form-control" rows="3" placeholder="Write something..." name="post-body"></textarea>
            </div>
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
                <span class="add-post__submit">
                    <button type="submit" class="btn btn-FB">Publish</button>
                </span>
            </div>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="../js/app.js"></script>
    <script src="../js/search.js" defer></script>
</body>

</html>