function preLoad() {
    if (!this.support.loading) {
        $.Alert("You need the Flash Player 9.028 or above to use SWFUpload.");
        return false;
    }
}
function loadFailed() {
    $.Alert("Something went wrong while loading SWFUpload. If this were a real application we'd clean up and then give you an alternative");
}

function fileQueued(file) {}

function fileQueueError(file, errorCode, message) {
    if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
        $.Alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
        return;
    }
    switch (errorCode) {
        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            $.Alert("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            $.Alert("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            $.Alert("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        default:
            $.Alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
    }
    $(this.movieElement).css({
        visibility:'visible'
    });
    $(this.movieElement.progress).remove();

}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
    this.startUpload();
}

function get_bytes( size ){
    var inKB = Math.round(size/1024);
    var inMB = Math.round(10*size/(1024*1024))/10;
    if( inMB > 1 ){
        return inMB + 'MB';
    }else{
        return inKB + 'K';
    }
}

function uploadStart(file) {
    $(this.movieElement).css({
        visibility:'hidden'
    });
    var self =	this;
    var div = $(
        "<table cellpadding='3' border='0'><tr>"
        +"<td>" + file['name'] + ' ' + get_bytes(file['size']) + "</td>"
        +"<td><div class='x-progress'><div class='x-progress-bar'></div></div></td>"
        +"<td><a class='cancel'><u>Hủy</u></a></td></tr></table>")
    .beforeTo(this.movieElement).k(0);

    $(div)
    .find(".cancel")
    .onClick( function(){
        self.cancelUpload();
    });

    this.movieElement.progress = div;

    if( this.appendUpload )
        return true;
    this.appendUpload = true;

    var settings = this.movieElement.parentNode.settings;
    settings.button_text = '<b><u>Đính kèm một tệp khác</u></b>';
    var add_div = $("<div>").afterTo(this.movieElement.parentNode).k(0);
    var swfu = new SWFUpload(settings);
    add_div.settings = settings;
    add_div.innerHTML = swfu.getFlashHTML();

    return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
    $(this.movieElement.progress)
    .set('title', percent +'%')
    .find("div.x-progress-bar")
    .css({
        width: percent +'%'
        });
}

function uploadSuccess(file, serverData) {}

function uploadError(file, errorCode, message) {
    switch (errorCode) {
        case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
            $.Alert("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
            $.Alert("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.IO_ERROR:
            $.Alert("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
            $.Alert("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
            $.Alert("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
            $.Alert("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
            if (this.getStats().files_queued === 0) {
            //document.getElementById(this.customSettings.cancelButtonId).disabled = true;
            }
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
            break;
        default:
            $.Alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
    }
    $(this.movieElement).css({
        visibility:'visible'
    });
    $(this.movieElement.progress).remove();
}

function uploadComplete(file) {
    try{
        $(this.movieElement.parentNode).remove();
    }catch(e){

    }
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {

}
