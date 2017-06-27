<?php
/* @var $this PlaylistsController */
/* @var $model Playlists */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'playlists-form',
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

    <?php if(!$isView): ?>
        <?php echo $form->errorSummary($model); ?>
        <?php  echo $form->errorSummary($stream); ?>
    <?php endif; ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>100, 'class'=>"form-control", 'readonly' => $isView)); ?>
        <?php echo $form->error($model,'name'); ?>
    </div>

    <div class="row">
    <table>
        <tr style="text-align:left">
            <td><?php echo $form->labelEx($model,'fromDatetime'); ?></td>
            <td><?php echo $form->labelEx($model,'toDatetime'); ?></td>
        </tr>
        <tr>
            <td><?php echo $form->textField($model,'fromDatetime', array('class'=>"form-control datepicker", 'disabled' => $isView)); ?></td>
            <td><?php echo $form->textField($model,'toDatetime', array('class'=>"form-control datepicker", 'disabled' => $isView)); ?></td>
        </tr>
        <tr>
            <td><?php echo $form->error($model,'fromDatetime'); ?></td>
            <td><?php echo $form->error($model,'toDatetime'); ?></td>
        </tr>
    </table>
    </div>

    <div class="row">
        <table>
            <tr style="text-align:left">
                <td><?php echo $form->labelEx($model,'fromTime'); ?></td>
                <td><?php echo $form->labelEx($model,'toTime'); ?></td>
            </tr>
            <tr>
                <td><?php echo $form->textField($model,'fromTime', array('class'=>"form-control timepicker", 'disabled' => $isView)); ?></td>
                <td><?php echo $form->textField($model,'toTime', array('class'=>"form-control timepicker", 'disabled' => $isView)); ?></td>
            </tr>
            <tr>
                <td><?php echo $form->error($model,'fromTime'); ?></td>
                <td><?php echo $form->error($model,'toTime'); ?></td>
            </tr>
        </table>
    </div>

    <div class="row" style='padding-bottom:1em;'>
        <?php echo $form->labelEx($model,'type'); ?>
        <?php echo $form->radioButtonList($model,'type',
            Playlists::$types,
            array('class'=>"type-control",
                'separator'=>' &nbsp; ',
                'labelOptions'=>array('style'=>'display:inline;'),
                'disabled' => $isView
            )
        ); ?>
        <?php echo $form->error($model,'type'); ?>
    </div>

    <div id="everyBlock" class="row">
        <table>
            <tr style="text-align:left">
                <td><?php echo $form->labelEx($model,'every'); ?></td>
            </tr>
            <tr>
                <td>    <?php echo $form->textField($model,'every',array('class'=>"form-control timepicker",
                            'value'=>$model->isNewRecord ? "00:30:00" : $model->every,
                            'readonly' => $isView)); ?>
                </td>
            </tr>
            <tr>
                <td><?php echo $form->error($model,'every'); ?></td>
            </tr>
        </table>
    </div>

    <div id="stream-url-block" class="row" >
        <?php echo $form->labelEx($stream,'url'); ?>
        <?php echo $form->textField($stream,'url', array('class'=>"form-control", 'disabled' => $isView)); ?>
        <?php echo $form->error($stream,'url'); ?>
    </div>

    <div class="row">
        <table>
            <tr style="text-align:left">
                <td><?php echo $form->labelEx($model,'sun'); ?></td>
                <td><?php echo $form->labelEx($model,'mon'); ?></td>
                <td><?php echo $form->labelEx($model,'tue'); ?></td>
                <td><?php echo $form->labelEx($model,'wed'); ?></td>
                <td><?php echo $form->labelEx($model,'thu'); ?></td>
                <td><?php echo $form->labelEx($model,'fri'); ?></td>
                <td><?php echo $form->labelEx($model,'sat'); ?></td>
            </tr>
            <tr>
                <td><?php echo $form->checkBox($model,'sun', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'mon', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'tue', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'wed', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'thu', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'fri', ['disabled' => $isView]); ?></td>
                <td><?php echo $form->checkBox($model,'sat', ['disabled' => $isView]); ?></td>
            </tr>
        </table>
    </div>

    <div class="row">
        <?php echo $form->error($model,'sun'); ?>
        <?php echo $form->error($model,'mon'); ?>
        <?php echo $form->error($model,'tue'); ?>
        <?php echo $form->error($model,'wed'); ?>
        <?php echo $form->error($model,'thu'); ?>
        <?php echo $form->error($model,'fri'); ?>
        <?php echo $form->error($model,'sat'); ?>
    </div>

    <div class="row">
        <?php echo $form->hiddenField($model,'author',array('value'=>Yii::app()->user->name)); ?>
    </div>

    <?php if(!$isView): ?>
        <div class="row buttons">
            <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>"form-control")); ?>
        </div>
    <?php endif; ?>

<?php $this->endWidget(); ?>

</div><!-- form -->
