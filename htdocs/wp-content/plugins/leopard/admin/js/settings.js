(function($) {
	"use strict"; 

	function run_upload_assets(data){
    	var count = (data.count > 0) ? data.count : 0;
    	var processed = (data.processed) ? data.processed : 0;
    	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					$('.progress-bar .progress').css({
                        width: '100%'
                    });
                    $('#percent').text('100%');
                    $('.progress_count').text( data.count + '/' + data.count );
                    $('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
                    setTimeout(function(){ 
						location.reload();
					}, 2000);
				}else{
					data.processed = result.data.processed;
					$('.progress-bar .progress').css({
                        width: result.data.percent + '%'
                    });
                    $('#percent').text(result.data.percent + '%');
                    data.count = result.data.count;
					run_upload_assets(data);
					$('.progress_count').text( processed + '/' + data.count );
				}
			}
		});
    }

	function upload_assets(){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.popup_title,
	        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">0%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
	        		count: 0,
			  		action: 'nou_leopard_offload_media_upload_assets',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	run_upload_assets(data);
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	function create_bucket(){
		var data = {
			bucket: $('#buckets-form input[name="nou_leopard_offload_media_bucket"]').val(),
			regional: $('#buckets-form select[name="nou_leopard_offload_media_bucket_regional"]').val(),
	  		action: 'nou_leopard_offload_media_create_bucket',
	  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
	  	};	
	  	$('.iziToast-body .spin_loading').css({
                        opacity: '1'
                    });
	  	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					setTimeout(function(){ 
						location.reload();
					}, 1000);
				}else{
					alert(result.data.message);
				}
				$('.iziToast-body .spin_loading').css({
                        opacity: '0'
                    });
			}
		});
	}

	function run_sync_data(data){
    	var count = (data.count > 0) ? data.count : 0;
    	var processed = (data.processed) ? data.processed : 0;
    	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					$('.progress-bar .progress').css({
                        width: '100%'
                    });
                    $('#percent').text('100%');
                    $('.progress_count').text( data.count + '/' + data.count );
                    $('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
                    setTimeout(function(){ 
						location.reload();
					}, 2000);
				}else{
					data.processed = result.data.processed;
					$('.progress-bar .progress').css({
                        width: result.data.percent + '%'
                    });
                    $('#percent').text(result.data.percent + '%');
                    data.count = result.data.count;
					run_sync_data(data);
					$('.progress_count').text( processed + '/' + data.count );
				}
			}
		});
	}

	function sync_data(count){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.popup_title,
	        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">0%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
	        		count: count,
			  		action: 'nou_leopard_offload_media_sync_data',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	run_sync_data(data);
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	function run_download_all_files(data){
		var count = (data.count > 0) ? data.count : 0;
    	var processed = (data.processed) ? data.processed : 0;
    	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					$('.progress-bar .progress').css({
                        width: '100%'
                    });
                    $('#percent').text('100%');
                    $('.progress_count').text( data.count + '/' + data.count );
                    $('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
                    setTimeout(function(){ 
						location.reload();
					}, 2000);
				}else{
					data.processed = result.data.processed;
					$('.progress-bar .progress').css({
                        width: result.data.percent + '%'
                    });
                    $('#percent').text(result.data.percent + '%');
                    data.count = result.data.count;
					run_download_all_files(data);
					$('.progress_count').text( processed + '/' + data.count );
				}
			}
		});
	}

	function run_remove_all_files_from_server(data){
		var count = (data.count > 0) ? data.count : 0;
    	var processed = (data.processed) ? data.processed : 0;
    	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					$('.progress-bar .progress').css({
                        width: '100%'
                    });
                    $('#percent').text('100%');
                    $('.progress_count').text( data.count + '/' + data.count );
                    $('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
                    setTimeout(function(){ 
						location.reload();
					}, 2000);
				}else{
					data.processed = result.data.processed;
					$('.progress-bar .progress').css({
                        width: result.data.percent + '%'
                    });
                    $('#percent').text(result.data.percent + '%');
                    data.count = result.data.count;
					run_remove_all_files_from_server(data);
					$('.progress_count').text( processed + '/' + data.count );
				}
			}
		});
	}

	function download_all_files(){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.download_title,
	        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">0%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
	        		count: 0,
			  		action: 'nou_leopard_offload_media_download_all_files',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	run_download_all_files(data);
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	function run_remove_all_files_from_bucket(data){
		var count = (data.count > 0) ? data.count : 0;
    	var processed = (data.processed) ? data.processed : 0;
    	$.ajax({
		  	url: leopard_wordpress_offload_media_params.ajax_url,
		  	type: "POST",
		  	data: data,
			success: function(result) {
				if(result.data.status == 'success'){
					$('.progress-bar .progress').css({
                        width: '100%'
                    });
                    $('#percent').text('100%');
                    $('.progress_count').text( data.count + '/' + data.count );
                    $('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
                    setTimeout(function(){ 
						location.reload();
					}, 2000);
				}else{
					data.processed = result.data.processed;
					$('.progress-bar .progress').css({
                        width: result.data.percent + '%'
                    });
                    $('#percent').text(result.data.percent + '%');
                    data.count = result.data.count;
					run_remove_all_files_from_bucket(data);
					$('.progress_count').text( processed + '/' + data.count );
				}
			}
		});
	}

	function remove_all_files_from_bucket(){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.remove_all_files_from_bucket_title,
	        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">0%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
	        		count: 0,
			  		action: 'nou_leopard_offload_media_remove_all_files_from_bucket',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	run_remove_all_files_from_bucket(data);
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	function remove_all_files_from_server(){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.remove_all_files_from_server_title,
	        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">0%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
	        		count: 0,
			  		action: 'nou_leopard_offload_media_remove_all_files_from_server',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	run_remove_all_files_from_server(data);
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	function copy_all_files_to_bucket(){
		iziToast.destroy();
		iziToast.show({
	        progressBar: false,
	        theme: 'dark',
	        progressBarColor: 'rgb(0, 255, 184)',
	        maxWidth: '500px',
	        drag: false,
	        overlay: true,
	        displayMode: 1,
	        pauseOnHover: false,
	        timeout: false,
	        title: leopard_wordpress_offload_media_params.copy_all_files_to_bucket_title,
	        //message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent">100%</span><span class="bar"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
	        position: 'topCenter',
	        onOpened: function () {
	        	var data = {
			  		action: 'nou_leopard_offload_media_copy_all_files_to_bucket',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	$.ajax({
				  	url: leopard_wordpress_offload_media_params.ajax_url,
				  	type: "POST",
				  	data: data,
					success: function(result) {
						setTimeout(function(){ 
							location.reload();
						}, 2000);
					}
				});
	        },
		    onClosing: function(instance, toast, closedBy){
		        setTimeout(function(){ 
					location.reload();
				}, 1000);
		    }
	    });
	}

	$(document).ready(function() {
        
        $('body').on('change', 'select[name="nou_leopard_wom_connection_provider"]', function(){
            var provider = $(this).val();
            $('#leopard-wordpress-offload-media-wrap .conditional').hide();
            $('#leopard-wordpress-offload-media-wrap .show_if_' + provider).show();
        });

        $("body").on('change', 'input[name="nou_leopard_offload_media_assets_rewrite_urls_checkbox"]', function() {
		    if(this.checked) {
		        $('#leopard-wordpress-offload-media-wrap .show_if_assets_rewrite_urls').show();
		    }else{
		    	$('#leopard-wordpress-offload-media-wrap .show_if_assets_rewrite_urls').hide();
		    }
		});

        $("body").on('click', '#nou_leopard_wom_scan_assets', function() {
		    iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.popup_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar"><span id="percent" style="line-height: 11px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.scan_title +'</span><span class="bar" style="opacity: 0;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpened: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scan_assets',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								$('#percent').text(result.data.total);
								$('.iziToast-body .iziToast-buttons').show();
								$('.iziToastloading').removeClass('spin_loading').addClass('dashicons-yes');
							}
						}
					});

		        },
		        buttons: [
			        ['<button>'+ leopard_wordpress_offload_media_params.upload_title +'</button>', function (instance, toast) {
			            upload_assets();
			        }],
			        ['<button>'+ leopard_wordpress_offload_media_params.close_title +'</button>', function (instance, toast) {
			            instance.hide({
			                transitionOut: 'fadeOutUp',
			            }, toast, 'buttonName');
			        }]
			    ],
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		}); 

		$("body").on('click', '#nou_leopard_wom_create_bucket', function() {
		    iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.popup_title,
		        message: '<div id="buckets-form"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_form_create_bucket',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								$('#buckets-form').html(result.data.form);
								$('.iziToast-body .iziToast-buttons').show();
								$('.iziToast-body .spin_loading').removeClass('revealIn');
							}
						}
					});

		        },
		        buttons: [
			        ['<div class="iziToastloading spin_loading"></div>'],
			        ['<button>'+ leopard_wordpress_offload_media_params.create_title +'</button>', function (instance, toast) {
			            var name = $('#buckets-form input[name="nou_leopard_offload_media_bucket"]').val();
			            if(name != ''){
			            	create_bucket();
			            }else{
			            	$('#buckets-form input[name="nou_leopard_offload_media_bucket"]').addClass('error');
			            }
			        }],
			        ['<button>'+ leopard_wordpress_offload_media_params.close_title +'</button>', function (instance, toast) {
			            instance.hide({
			                transitionOut: 'fadeOutUp',
			            }, toast, 'buttonName');
			        }]
			    ],
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		});

		$("body").on('click', '#nou_leopard_wom_sync_data, #nou_leopard_wom_resync_data', function() {
			iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.popup_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar" style="overflow: visible;"><span id="percent" style="line-height: 15px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.sync_title +'</span><span class="bar" style="opacity: 0;height: 15px;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scaned_sync_data',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								if(result.data.count > 0){
									setTimeout(function(){ 
										location.reload();
									}, 1000);
								}else{
									$('#percent').text(result.data.message);
									setTimeout(function(){ 
										location.reload();
									}, 3000);
								}
							}else{
								$('#percent').text(result.data.message);
							}
						}
					});

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		}); 

		$("body").on('click', '#nou_leopard_wom_settings_download_files_from_bucket', function() {
			iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.download_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar" style="overflow: visible;"><span id="percent" style="line-height: 15px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.scan_title +'</span><span class="bar" style="opacity: 0;height: 15px;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scan_attachments',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								download_all_files();
							}else{
								$('#percent').text(result.data.message);
							}
						}
					});

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		});

		$("body").on('click', '#nou_leopard_wom_settings_remove_files_from_bucket', function() {
			iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.remove_all_files_from_bucket_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar" style="overflow: visible;"><span id="percent" style="line-height: 15px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.scan_title +'</span><span class="bar" style="opacity: 0;height: 15px;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scan_attachments',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								remove_all_files_from_bucket();
							}else{
								$('#percent').text(result.data.message);
							}
						}
					});

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		});  

		$("body").on('click', '#nou_leopard_wom_settings_remove_files_from_server', function() {
			iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.remove_all_files_from_server_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar" style="overflow: visible;"><span id="percent" style="line-height: 15px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.scan_title +'</span><span class="bar" style="opacity: 0;height: 15px;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scan_attachments',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								remove_all_files_from_server();
							}else{
								$('#percent').text(result.data.message);
							}
						}
					});

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		}); 

		$("body").on('click', '#nou_leopard_wom_settings_copy_files_to_bucket_kill', function() {
			iziToast.show({
			    progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
			    title: leopard_wordpress_offload_media_params.copy_all_files_to_bucket_title,
			    message: leopard_wordpress_offload_media_params.confirm_kill_process,
			    position: 'topCenter',
			    buttons: [
			        ['<button>'+ leopard_wordpress_offload_media_params.confirm_kill_process_btn +'</button>', function (instance, toast) {
			            var data = {
					  		action: 'nou_leopard_offload_media_copy_all_files_to_bucket_kill_process',
					  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
					  	};		
					  	$.ajax({
						  	url: leopard_wordpress_offload_media_params.ajax_url,
						  	type: "POST",
						  	data: data,
							success: function(result) {
								setTimeout(function(){ 
									location.reload();
								}, 1000);
							}
						});
			        }, true], // true to focus
			        ['<button>'+ leopard_wordpress_offload_media_params.close_title +'</button>', function (instance, toast) {
			            instance.hide({
			                transitionOut: 'fadeOutUp',
			                onClosing: function(instance, toast, closedBy){
			                    console.info('closedBy: ' + closedBy); // The return will be: 'closedBy: buttonName'
			                }
			            }, toast, 'buttonName');
			        }]
			    ],
			    onOpened: function () {
		        	$('.iziToast-body .iziToast-buttons').show();

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
			});
		}); 

		$("body").on('click', '#nou_leopard_wom_settings_copy_files_to_bucket', function() {
			iziToast.show({
		        progressBar: false,
		        theme: 'dark',
		        progressBarColor: 'rgb(0, 255, 184)',
		        maxWidth: '500px',
		        drag: false,
		        overlay: true,
		        displayMode: 1,
		        pauseOnHover: false,
		        timeout: false,
		        title: leopard_wordpress_offload_media_params.copy_all_files_to_bucket_title,
		        message: '<div class="iziToastloading spin_loading"></div> ' + '<div class="progress-bar" style="overflow: visible;"><span id="percent" style="line-height: 15px;height: 15px;right: -5%;width: 100%;">'+ leopard_wordpress_offload_media_params.scan_title +'</span><span class="bar" style="opacity: 0;height: 15px;"><span class="progress"></span></span></div>' + '<div class="progress_count"></div>',
		        position: 'topCenter',
		        onOpening: function () {
		        	var data = {
				  		action: 'nou_leopard_offload_media_scan_attachments',
				  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce,
				  		do_action: 'copy',
				  	};		
				  	$.ajax({
					  	url: leopard_wordpress_offload_media_params.ajax_url,
					  	type: "POST",
					  	data: data,
						success: function(result) {
							if(result.data.status == 'success'){
								copy_all_files_to_bucket();
							}else{
								$('#percent').text(result.data.message);
							}
						}
					});

		        },
			    onClosing: function(instance, toast, closedBy){
			        setTimeout(function(){ 
						location.reload();
					}, 1000);
			    }
		    });
		}); 

		$("body").on('click', 'input[name="nou_leopard_offload_media_sync_target"]', function() {
			var type = $(this).val();
			var data = {
				type: type,
		  		action: 'nou_leopard_offload_media_sync_render_form',
		  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
		  	};		
		  	$.ajax({
			  	url: leopard_wordpress_offload_media_params.ajax_url,
			  	type: "POST",
			  	data: data,
			  	beforeSend: function(){
			  		$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').addClass('active');
			  	},
				success: function(result) {
					if(result.data.status == 'success'){
						$('body #leopard-wordpress-offload-media-wrap .sync-content').html(result.data.html);
						if(type == 'bucket'){
							$('body #leopard-wordpress-offload-media-wrap .sync-content .sync-content-bucket').removeClass('hidden');
							$('body #leopard-wordpress-offload-media-wrap .sync-content .sync-content-provider-to').addClass('hidden');
						}else{
							$('body #leopard-wordpress-offload-media-wrap .sync-content .sync-content-bucket').removeClass('hidden');
							$('body #leopard-wordpress-offload-media-wrap .sync-content .sync-content-provider').removeClass('hidden');
						}
					}
					$('body #leopard-wordpress-offload-media-wrap .sync-action').addClass('hidden');
					$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').removeClass('active');
				}
			});
		});

		$("body").on('change', '#leopard-wordpress-offload-media-wrap .sync-provider', function() {
			var type = $(this).data('target');
			var provider = $(this).val();
			var class_condictions = $('body #leopard-wordpress-offload-media-wrap .conditional_'+type);
			var class_target = $('body #leopard-wordpress-offload-media-wrap .conditional_'+ type +'.show_if_'+provider);
			class_condictions.addClass('hidden');
			class_target.removeClass('hidden');

			class_condictions.find('.conditional_change').val('');

			$('body #leopard-wordpress-offload-media-wrap .sync-content-bucket').addClass('hidden');
			$('body #leopard-wordpress-offload-media-wrap .sync-action').addClass('hidden');
		});

		$("body").on('change', '#leopard-wordpress-offload-media-wrap .conditional_change', function() {
			var parent = $(this).closest('.sync-content-provider-col');
			var type = 'from';
			
			if(parent.hasClass('sync-content-provider-to')){
				type = 'to';
			}
			var access_key = parent.find('input[name="nou_leopard_wom_connection_access_key_text_'+ type +'"]').val();
			var secret_access_key = parent.find('input[name="nou_leopard_wom_connection_secret_access_key_text_'+ type +'"]').val();
			var credentials_key = parent.find('textarea[name="nou_leopard_wom_connection_credentials_'+ type +'"]').val();
			var provider = $('.sync-content-provider-'+ type +' .sync-provider').val();
			var region = $('.sync-content-provider-'+ type +' .sync-region').val();

			if(provider == '0'){
				alert(leopard_wordpress_offload_media_params.sync_provider_required);
				return false;
			}

			if((provider == 'google' && credentials_key != '') || (access_key != '' && secret_access_key != '')){
				var data = {
					type: type,
					provider: parent.find('select[name="nou_leopard_wom_connection_provider_'+ type +'"]').val(),
					region: region,
					access_key: parent.find('input[name="nou_leopard_wom_connection_access_key_text_'+ type +'"]').val(),
					secret_access_key: parent.find('input[name="nou_leopard_wom_connection_secret_access_key_text_'+ type +'"]').val(),
					credentials_key: parent.find('textarea[name="nou_leopard_wom_connection_credentials_'+ type +'"]').val(),
			  		action: 'nou_leopard_offload_media_sync_render_bucket_form',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};		
			  	$.ajax({
				  	url: leopard_wordpress_offload_media_params.ajax_url,
				  	type: "POST",
				  	data: data,
				  	beforeSend: function(){
				  		$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').addClass('active');
				  	},
					success: function(result) {
						if(result.data.status == 'success'){
							$('body .sync-content-provider-'+ type +' .sync-content-bucket').html(result.data.html).removeClass('hidden');
						}
						$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').removeClass('active');
					}
				});
			}

			$('body #leopard-wordpress-offload-media-wrap .sync-action').addClass('hidden');
		});

		$("body").on('change', '#leopard-wordpress-offload-media-wrap .sync-content-bucket select', function() {
			var parent = $(this).closest('.sync-content-provider-col');
			var type = $(this).data('target');
		
			var data = {
				type: type,
				bucket: $(this).val(),
		  		action: 'nou_leopard_offload_media_sync_update_bucket_selected',
		  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
		  	};		
		  	$.ajax({
			  	url: leopard_wordpress_offload_media_params.ajax_url,
			  	type: "POST",
			  	data: data,
			  	beforeSend: function(){
			  		$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').addClass('active');
			  	},
				success: function(result) {
					if(result.data.status == 'done'){
						$('body #leopard-wordpress-offload-media-wrap .sync-action').removeClass('hidden');
					}
					$('body #leopard-wordpress-offload-media-wrap .nou_leopard_wom_loading').removeClass('active');
				}
			});
		});  

		if($('.leopard-sync-notice .current-sync-process').length){
			function report_sync_data() {
				var data = {
			  		action: 'nou_leopard_offload_media_report_sync_data',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};	
			    $.ajax({
				  	url: leopard_wordpress_offload_media_params.ajax_url,
				  	type: "POST",
				  	data: data,
					success: function(result) {
						if(result.data.status == 'success'){
							if(result.data.message <= 100){
								$('.leopard-sync-notice .progress-bar .progress').css({
			                        width: result.data.message + '%'
			                    });
								$('.leopard-sync-notice .current-sync-process').text(result.data.message + '%');
							}else{
								setTimeout(function(){ 
									location.reload();
								}, 1000);
							}
							$('.leopard-sync-notice').removeClass('hidden');
						}
						if(result.data.sync == 1){
							setTimeout(function(){ 
								location.reload();
							}, 1000);
						}
					}
				});
			}
			setInterval(report_sync_data, 30000);
			report_sync_data();
		}

		//copy_all_files_to_bucket_check_process
		if($('#nou_leopard_wom_settings_copy_files_to_bucket_kill').length){
			function copy_all_files_to_bucket_check_process() {
				var data = {
			  		action: 'nou_leopard_offload_media_copy_all_files_to_bucket_check_process',
			  		_wpnonce: leopard_wordpress_offload_media_params.ajax_nonce
			  	};	
			    $.ajax({
				  	url: leopard_wordpress_offload_media_params.ajax_url,
				  	type: "POST",
				  	data: data,
					success: function(result) {
						if(result.data.status == 'success'){
							if(result.data.message <= 100){
								$('#copy_files_to_bucket_card .progress-bar .progress').css({
			                        width: result.data.message + '%'
			                    });
								$('#copy_files_to_bucket_card #percent').text(result.data.message + '%');
								$('#copy_files_to_bucket_card .current-sync-process').text(result.data.count);
							}else{
								setTimeout(function(){ 
									location.reload();
								}, 1000);
							}
						}
						if(result.data.sync == 2){
							setTimeout(function(){ 
								location.reload();
							}, 1000);
						}
					}
				});
			}
			setInterval(copy_all_files_to_bucket_check_process, 50000);
			copy_all_files_to_bucket_check_process();
		}

    });
	
	$('#nou_leopard_wom_update_cache_control').on('click', function(){
		$('input[name="nou_leopard_wom_update_cache_control"]').val('1');
		$('#leopard-wordpress-offload-media-wrap form').submit();
	});
})(jQuery);