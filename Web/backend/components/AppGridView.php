<?php

namespace backend\components;

use yii\grid\GridView;

class AppGridView extends GridView
{
    public $tableOptions = ['class' => 'table table-bordered table-hover table-striped mb-0'];
    public $summaryOptions = ['class' => 'text-muted small'];
    public $layout = '
        <div class="table-responsive">
            <div class="p-0">
                {items}
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>{summary}</div>
                <div>{pager}</div>
            </div>
        </div>';
}
