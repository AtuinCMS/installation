<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/** @var $model atuin\installation\models\ModelInstallation */
/** @var $model_user amnah\yii2\user\models\User */
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

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-5\">{input}</div>\n<div class=\"col-md-7\">{error}</div>",
            ],
        ]); ?>
        <div class="col-md-10 col-md-offset-2">
            <?= $form->field($model, 'title')->textInput(['placeholder' => $model->getAttributeLabel('title')])->label(FALSE) ?>
            <?= $form->field($model_user, 'email')->textInput(['placeholder' => $model_user->getAttributeLabel('email')])->label(FALSE) ?>
            <?= $form->field($model_user, 'username')->textInput(['placeholder' => $model_user->getAttributeLabel('username')])->label(FALSE) ?>
            <?= $form->field($model_user, 'newPassword')->passwordInput(['placeholder' => $model_user->getAttributeLabel('newPassword')])->label(FALSE) ?>
            <?= $form->field($model_user, 'newPasswordConfirm')->passwordInput(['placeholder' => $model_user->getAttributeLabel('newPasswordConfirm')])->label(FALSE) ?>
        </div>
        <div class="box-footer col-xs-12">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>


        <?php ActiveForm::end() ?>

    </div>
</div>




