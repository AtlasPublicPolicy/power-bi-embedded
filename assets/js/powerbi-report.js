(function($) {
    "use strict";
    $(document).ready(function() {
        let models = window['powerbi-client'].models;	
        let restURL = powerBiEmbed.rest_url + 'wp/v2/powerbi/';
        let container = $('.powerbi-embed');
        let breakpoint = powerBiEmbed.mobile_breakpoint
        window.report = {};
        
        function getToken(){
            return new Promise(function(resolve, reject){
                $.ajax({
                    url: restURL + 'getToken',
                    beforeSend: function(xhr){
                        xhr.setRequestHeader('X-WP-Nonce', powerBiEmbed.nonce);
                    },
                    type: 'GET',
                    dataType: 'json',
                }).done(function(response){
                    resolve(response.replace(/"/g,""));
                }).fail(function(){
                    resolve(false);
                });
            });
        }

        function getReportData(postID){
            return new Promise(function(resolve, reject){
                $.ajax({
                    url: restURL + 'getReportData?post_id=' + postID,
                    beforeSend: function(xhr){
                        xhr.setRequestHeader('X-WP-Nonce', powerBiEmbed.nonce);
                    },
                    type: 'GET',
                    dataType: 'json',
                }).done(function(response){
                    resolve(response);
                }).fail(function(){
                    resolve(false);
                });
            });
        }

        function isTokenValid(report) {
            setTimeout(function(){
                getToken().then(function(data){
                    console.log('Resetting token: ' + report.getAccessToken());
                    report.setAccessToken(data)
                        .then(function(resp) {
                            console.log('New token: ' + report.getAccessToken());
                            sessionStorage.setItem('access_token', report.getAccessToken() );
                        })
                        .catch(function(error) {console.log(error)} );

                    isTokenValid(report);
                }).catch(function(error) { console.log(error)});
            }, 1000*60*10);
        }

        window.changePowerBIPage = function(pageName){
            window.report.setPage(pageName);
        }

        async function processReportData(postID){
            let access_token = await getToken();
            sessionStorage.setItem('access_token', access_token);
            let reportData = await getReportData(postID)
            let embedConfiguration = {
                type: reportData.embed_type,
                embedUrl: reportData.embed_url,
                tokenType: models.TokenType[reportData.token_type],
                accessToken: access_token,
                settings: {
                    filterPaneEnabled: reportData.filter_pane === 'on' ? true : false,
                    navContentPaneEnabled: reportData.page_navigation === 'on' ? true : false,
                    background: reportData.background !== '' ? reportData.background : models.BackgroundType.Transparent,
                    localeSettings: {
                        language: reportData.language,
                        formatLocale: reportData.format_local,
                    }
                },
            };
            if(breakpoint !== '' && window.innerWidth <= Number(breakpoint)) embedConfiguration.settings.layoutType = models.LayoutType.MobilePortrait
            switch(reportData.embed_type){
                case 'dashboard': {
                    embedConfiguration.dashboardId = reportData.dashboard_id;
                    break;
                }
                case 'report': {
                    embedConfiguration.id = reportData.report_id;
				    embedConfiguration.pageName = reportData.page_name;
                    break;
                }
                case 'qna': {
                    embedConfiguration.viewMode = models.QnaMode[reportData.qna_mode];
                    embedConfiguration.atasetIds = reportData.dataset_id;
                    embedConfiguration.question = reportData.input_question;
                    break;
                }
                case 'visual': {
                    embedConfiguration.pageName = reportData.page_name;
                    embedConfiguration.visualName = reportData.visual_name;
                    embedConfiguration.id = reportData.report_id;
                    break;
                }
                case 'tile': {
                    embedConfiguration.id = reportData.tile_id;
                    embedConfiguration.dashboardId = reportData.dashboard_id;
                    break;
                }
                case 'edit': {
                    embedConfiguration.viewMode = models.ViewMode.Edit;
                    embedConfiguration.permissions = models.Permissions.All;
                    break;
                }
                case 'create': {
                    embedConfiguration.datasetId = reportData.dataset_id;
                    embedConfiguration.permissions = models.Permissions.All;
                    break;
                }
            }
            // ****
            // apply filters before report load
            // ****
            
            // get query string and convert to powerbi filter
            let urlParams = new URLSearchParams(window.location.search);

            // if filters value exists parse the encoded string to JSON and set as filter
            if ( urlParams.has('filters') ) {
                let urlFilters = JSON.parse(urlParams.get('filters'));
                let filters = urlFilters;
                embedConfiguration.filters = filters;
            }

            // ****
            // apply slicers before report load
            // ****
            if ( urlParams.has('slicers') ) {
                let urlSlicers = JSON.parse(urlParams.get('slicers'));
                embedConfiguration.slicers = urlSlicers;
            }

            window.report = 'create' === reportData.report_mode && 'report' === reportData.embed_type ? powerbi.createReport(container.get(0), embedConfiguration) : powerbi.embed(container.get(0), embedConfiguration);
            // set timeOut to refresh token
			window.report.on('loaded', function(event) {
                isTokenValid(report);
            });
            if(breakpoint !== '' && window.innerWidth <= Number(breakpoint)){
                let mobileWidth = reportData.mobile_width ? reportData.mobile_width : '100%';
                let mobileHeight = reportData.mobile_height ? reportData.mobile_height : 'auto';
                container.width(mobileWidth);
                container.height(mobileHeight);
                window.report.on('rendered', function(e){
                    let pages = report.getPages().then(pages => {
                        if(pages.length && reportData.page_navigation){
                            //create mobile nav
                            let pagesHTML = '';
                            for(let page in pages){
                                pagesHTML += pages[page].isActive ? `<li class="active">${pages[page].displayName}</li>` : `<li onclick="changePowerBIPage('${pages[page].name}')">${pages[page].displayName}</li>`; 
                            }
                            let mobileNav = `
                                <style>
                                    .powerbi_page_nav {
                                        list-style:none; 
                                        cursor:pointer;
                                        padding: 0;
                                    }
                                    .powerbi_page_nav li {
                                        text-align:center;
                                        padding:15px 0;
                                        width:100%;
                                        border-bottom:1px solid;
                                        background-color: #f3f2f1;
                                        font-size: 16px;
                                    }
                                    .powerbi_page_nav li.active {
                                        background-color:#fff;
                                        border-bottom: 4px solid #f2c811;
                                    }
                                </style>
                                <ul class="powerbi_page_nav">
                                    ${pagesHTML}
                                </ul>    
                            `;
                            let mobileNavE = $('.powerbi_page_nav');
                            if (mobileNavE.length){
                                mobileNavE.html(mobileNav);
                            } else {
                                container.after(mobileNav);
                            }
                            
                        }
                    });
                });
            }
        }
    

        if(container.length){
            let postID = container.data('postid');
            processReportData(postID); 
        }
    })
    
})(jQuery);