<?php
/* @var $this PointController */
/* @var $model Point */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'point-form',
    'enableAjaxValidation'=>false,
)); ?>

<?php
    $isView = false;
    if(isset($isViewForm)
        && ($isViewForm === true)
    ) {
        $isView = true;
    }
?>

    <?php if(!$isView): ?>
        <p class="note">Fields with <span class="required">*</span> are required.</p>
    <?php endif; ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255, 'class'=>"form-control", 'readonly' => $isView)); ?>
        <?php echo $form->error($model,'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'ip'); ?>
        <?php echo $form->textField($model,'ip',array('size'=>60,'maxlength'=>255, 'class'=>"form-control", 'readonly' => $isView)); ?>
        <?php echo $form->error($model,'ip'); ?>
    </div>

    <div class="row">
        <?php echo $form->hiddenField($model,'username', array('value'=>Yii::app()->user->name)); ?>
    </div>

    <?php $this->renderPartial('sections/_volume', [
        'model' => $model,
        'form' => $form,
        'isView' => $isView
    ]); ?>

    <div class="row">
    <?= $form->labelEx($model, 'TVschedule'); ?>
    <?php $this->widget('TVscheduleWidget', [
        'tvBlocks' => $model->tv,
        'editable' => !$isView
    ]); ?>
    </div>

    <div class="row">
    <?= $form->labelEx($model, 'channels'); ?>
    <?php $this->widget('PointChannelsWidget', [
        'playlistToPoint' => $model->playlistToPoint,
        'editable' => !$isView
    ]); ?>
    </div>

    <div class="row">
    <?= $form->labelEx($model, 'screen_id'); ?>
    <?php $this->widget('ScreenSelectorWidget', [
        'point' => $model,
        'screens' => $screens,
        'editable' => !$isView
    ]); ?>
    </div>

    <?php if (!$isView): ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>"form-control")); ?>
        </div>
    <?php endif; ?>

<?php $this->endWidget(); ?>

</div>
<!-- form -->

<?php if (!$isView) {
$this->widget('ChooseWidgetDialogWidget', [
  'widgets' => $widgets,
]); } ?>

<?php
if (!$isView) {
    foreach (Playlists::$types as $type => $name) {
        $this->widget('ChoosePlaylistDialogWidget', [
          'channelType' => $type, // 0, 1, 2
          'channelName' => $name, 
          'playlists' => isset($playlists[$type]) ? $playlists[$type] : [],
        ]);
    }
}
?>
