    /*弹框JS内容*/
    jQuery(document).ready(function($){
		
		// 添加
		$('.cd-popup-trigger-add').click(function(){
			$('.cd-popup-add').addClass('is-visible');
		});
		
		// 打开
		$('.cd-popup-trigger-view').click(function(){
			var id = $(this).closest('tr').find('.td-id').text();
			
			$('.cd-popup-view').each(function(){
				if($(this).data('id') == id){
					$(this).addClass('is-visible');
				}
			});
		});
		
		//打开窗口
        //$('.cd-popup-trigger').on('click', function(event){
        //    event.preventDefault();
        //    $('.cd-popup').addClass('is-visible');
        //    //$(".dialog-addquxiao").hide()
        //});
        //关闭窗口
        $('.cd-popup').on('click', function(event){
            if( $(event.target).is('.cd-popup-close') || $(event.target).is('.cd-popup') ) {
                event.preventDefault();
                $(this).removeClass('is-visible');
            }
        });
        //ESC关闭
        $(document).keyup(function(event){
            if(event.which=='27'){
                $('.cd-popup').removeClass('is-visible');
            }
        });


    });
