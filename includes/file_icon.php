<?php
function getFileIcon($ext) {
    switch($ext) {
        case 'pdf':
            return 'bi-file-earmark-pdf-fill text-danger';
        case 'doc':
        case 'docx':
            return 'bi-file-earmark-word-fill text-primary';
        case 'xls':
        case 'xlsx':
            return 'bi-file-earmark-excel-fill text-success';
        case 'ppt':
        case 'pptx':
            return 'bi-file-earmark-ppt-fill text-warning';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'bi-file-earmark-image-fill text-info';
        case 'txt':
        case 'csv':
        case 'log':
            return 'bi-file-earmark-text-fill text-secondary';
        case 'zip':
        case 'rar':
            return 'bi-file-earmark-zip-fill text-warning';
        default:
            return 'bi-file-earmark-fill text-secondary';
    }
}