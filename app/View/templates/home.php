<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(\App\View\View::includeCSS('style.css')); ?>">
    <title><?php echo htmlspecialchars($title); ?></title>
</head>
<body>
    <div class="wrapper">
        <div class="wrapper__blocs">
        <section class="sidebar">
            <div class="sidebar__top">
                <form action="#" class="sidebar__form">
                    <input placeholder="Наименование директории" type="text" class="sidebar__input">
                    <div class="sidebar__btns">
                        <button  class="sidebar__btn">Добавить папку</button>
                        <button class="sidebar__btn">Добавить файл</button>
                        <button class="sidebar__btn">Удалить</button>
                    </div>
                </form>
            </div>
            <div class="sidebar__bottom">
<!--                <ul class="sidebar__directories">-->
                    <?php echo $data['directories'] ?>
<!--                </ul>-->
            </div>
        </section>
        <section class="content">
            <div class="content__top">
                <h2 class="content__title">Выбрано: </h2>
                <button class="content__btn">Скачать</button>
            </div>
            <div class="content__bottom">
                <img class="no-img" src="/images/no-photo.png" alt="">
            </div>
        </section>
        </div>
    </div>

    <script type="module" src="<?php echo htmlspecialchars(\App\View\View::includeJS('app.js')); ?>"></></script>
</body>
</html>
