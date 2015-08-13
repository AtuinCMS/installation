<?php

use yii\helpers\Html;


\atuin\installation\assets\AppInstallAsset::register($this);

/** @var $model atuin\installation\models\ModelInstallation */
/** @var $form_title string */
/** @var $page_info string */

$this->params['form_title'] = $form_title;
?>


<div class="callout callout-info">
    <h4>Instructions:</h4>

    <p>
        <?= $page_info ?>
    </p>
</div>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->params['form_title']) ?></h3>
    </div>
    <div class="box-body">

</div>
</div>
