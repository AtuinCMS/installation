<?php
use atuin\installation\assets\AppAsset;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);

$this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->params['form_title']) ?></title>
    <?php $this->head() ?>
</head>
<body class="skin-blue-light layout-top-nav">
<?php $this->beginBody() ?>
<div class="wrapper">
    <header class="main-header">
        <?php
        NavBar::begin([
            'brandLabel' => 'Atuin system installation',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-static-top',
            ],
        ]);
        NavBar::end();
        ?>
    </header>
    <div class="content-wrapper">
        <div class="container">

            <section class="content-header">
                <h1>A'tuin system installation
                    <small>Version <?= \atuin\engine\Module::getVersion() ?></small>
                </h1>
                <?= Breadcrumbs::widget([

                    'tag' => 'ol',
                    'homeLink' => [
                        'label' => '<i class="fa fa-dashboard"></i>Home',
                        'url' => '#',
                        'encode' => false,
                    ],
                    'links' => [
                        [
                            'label' => 'Atuin installation',
                        ],
                    ],
                ]) ?>
            </section>
            <section class="content">
                <?= $content ?>
            </section>
        </div>
    </div>
</div>

<footer class="main-footer">
    <div class="container">
        <p class="pull-left">&copy; Atuin <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
