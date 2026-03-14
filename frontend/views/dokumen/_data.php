<?php

use yii\helpers\Html;
use frontend\models\DataLampiran;

$domain = yii\helpers\Url::base(true);

?>

<style>
    .search-result-item {
        display: block;
        padding: 1rem;
        border: 1px solid #eee;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .search-result-item:hover {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }

    .search-result-meta {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .meta-badge {
        color: #475569;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .search-result-title {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 1.25rem;
    }

    .search-result-title a {
        color: #1a2752;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .search-result-title a:hover {
        color: #3b82f6;
    }

    .search-result-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn-doc-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .btn-doc-primary {
        background-color: #f8fafc;
        color: #1a2752;
        border: 1px solid #e2e8f0;
    }

    .btn-doc-primary:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #1a2752;
        transform: translateY(-1px);
    }

    .btn-doc-outline:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        color: #1a2752;
        transform: translateY(-1px);
    }

    /* Results Layout */
    .results-container,
    .results-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
    }

    .results-list .item {
        display: block !important;
    }

    .results-list .item:last-child {
        margin-bottom: 0 !important;
    }
</style>



<div class="search-result-item">
    <div class="search-result-meta">
        <span class="meta-badge">
            <?= Html::a($model->bentuk_peraturan ?: 'Dokumen', ['/dokumen/index2', 'id' => $model->bentuk_peraturan], ['class' => 'text-decoration-none', 'style' => 'color: inherit;']); ?>
        </span>
        <span style="color: #cbd5e1;">&bull;</span>
        <span><?= $model->tahun_terbit ?: '-'; ?></span>
    </div>

    <h3 class="search-result-title">
        <?= Html::a($model->judul, ['/dokumen/view', 'id' => $model->id], ['title' => 'Lihat detail dokumen']); ?>
    </h3>

    <div class="search-result-actions">
        <?php
        $lampiran = DataLampiran::find()->where(['id_dokumen' => $model->id])->one();

        if (!empty($lampiran)) {
            echo Html::a('<i class="bi bi-file-earmark-pdf text-danger"></i> Dokumen', ['/common/dokumen/' . $lampiran->dokumen_lampiran], [
                'class' => 'btn-doc-action btn-doc-primary',
                'target' => '_blank',
                'title' => 'Unduh/Lihat Dokumen'
            ]);
        }

        if (!empty($model->abstrak)) {
            echo Html::a('<i class="bi bi-file-earmark-text text-primary"></i> Abstrak', ['/common/dokumen/' . $model->abstrak], [
                'class' => 'btn-doc-action btn-doc-outline',
                'target' => '_blank',
                'title' => 'Unduh/Lihat Abstrak'
            ]);
        }
        ?>
    </div>
</div>