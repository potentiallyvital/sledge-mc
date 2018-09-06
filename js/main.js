$(document).ready(function()
{
        doInputs();
        doModal();
});

function doInputs()
{
        $('input[type=text]').each(function()
        {
                $(this).attr('autocomplete', 'off');
        });
}

function doModal()
{
        $(document).on('keydown', function(e)
        {
                switch (e.which)
                {
                        case 27:
                        case 8:
                        case 32:
                        case 13:
                                $('#shadow').hide();
                                break;
                }
        });

        $(document).on('click', '#modal-close', function()
        {
                $('#shadow').hide();
        });
}

$(document).ajaxComplete(function()
{
});

(function($){
        $.fn.extend({
                loading: function(){
                        var style = '';
                        style += 'position:absolute;';
                        style += 'top:'+$(this).offset().top+'px;';
                        style += 'left:'+$(this).offset().left+'px;';
                        style += 'height:'+$(this).outerHeight()+'px;';
                        style += 'width:'+$(this).outerWidth()+'px;';
                        style += 'background-color:rgba(255,255,255,0.3);';
                        style += 'z-index:'+$(this).css('z-index')+';';
                        style += 'border:1px solid rgba(255,255,255,0.5);';
                        style += 'border-radius:'+($(this).hasClass('panel') ? 10 : 0)+'px;';
                        var cover = $('<div id="'+$(this).attr('id')+'-loading'+'" class="loading" style="'+style+'"></div>');
                        cover.insertBefore($('body div:first'));
                        return cover;
                },
                done: function(){
                        var id = $(this).attr('id')+'-loading';
                        $('#'+id).remove();
                },
        });
}(jQuery));


function target(e)
{
    return $(e.target || e.srcElement || e.originalTarget);
}
