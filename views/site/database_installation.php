<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;


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

        <?php
        $form = ActiveForm::begin([
            'layout' => 'horizontal',
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-5\">{input}</div>\n<div class=\"col-md-7\">{error}</div>",
            ],

        ]); ?>
        <div class="col-md-10 col-md-offset-2">
            <?= $form->field($model, 'host')->textInput(['placeholder' => $model->getAttributeLabel('host')])->label(FALSE) ?>
            <?= $form->field($model, 'dbname')->textInput(['placeholder' => $model->getAttributeLabel('dbname')])->label(FALSE) ?>
            <?= $form->field($model, 'db_username')->textInput(['placeholder' => $model->getAttributeLabel('db_username')])->label(FALSE) ?>
            <?= $form->field($model, 'db_password')->textInput(['placeholder' => $model->getAttributeLabel('db_password')])->label(FALSE) ?>
            <?= $form->field($model, 'charset')->widget(\kartik\select2\Select2::classname(), [
                'data' => $model->charsetList(),
                'theme' => \kartik\select2\Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => 'Select a charset ...'],
                'pluginOptions' => [
                ],
            ])->label(FALSE) ?>
        </div>
        <div class="box-footer col-xs-12">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end() ?>

    </div>
</div>




