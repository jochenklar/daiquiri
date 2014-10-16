var app = angular.module('query',['table']);

app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.defaults.headers.common['Accept'] = 'application/json';
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
}]);

app.factory('AccountService', ['$http','$timeout','$q',function($http,$timeout,$q) {
    var account = {
        active: {
            form: false,
            job: false
        },
        database: {},
        job: {},
        jobs: []
    };

    return {
        account: account,
        fetchAccount: function() {
            $http.get('query/account/').success(function(response) {
                account.jobs = response.jobs;
                account.database = response.database;
            });
        },
        fetchJob: function(id) {
            var deferred = $q.defer();

            $http.get('query/account/show-job/id/' + id).success(function(response) {
                account.job = response.job;
                deferred.resolve();
            });

            return deferred.promise;
        }
    };
}]);

app.factory('FormService', ['$http','AccountService',function($http,AccountService) {
    var values = {};
    var errors = {};

    return {
        values: values,
        errors: errors,
        submitQuery: function(formName) {
            var data = {};
            data[formName + '_csrf'] = $('#' + formName + '_csrf').attr('value');

            // merge with form values
            angular.extend(data,values[formName]);

            $http.post('query/form/?form=' + formName,$.param(data)).success(function(response) {
                if (response['status'] == 'ok') {
                    AccountService.fetchAccount();
                } else if (response['status'] == 'error') {
                    errors[formName] = {};
                    angular.forEach(response['errors'], function(object, key) {
                        errors[formName][key] = object;
                    });
                } else {
                    errors[formName] = {
                        'form': ['Unknown response.']
                    };
                }
            }).error(function () {
                errors[formName] = {
                    'form': ['Could not connect to server.']
                };
            });
        }
    };
}]);

app.factory('PlotService', ['$http','AccountService',function($http,AccountService) {
    var values = {};
    var errors = {};

    return {
        values: values,
        errors: errors,
        createPlot: function() {
            // manual validation
            // var valid = true;
            // if (angular.isUndefined(values.plot_x)) {
            //     errors.plot_x = true;
            //     valid = false;
            // }
            // if (angular.isUndefined(values.plot_y)) {
            //     errors.plot_y = true;
            //     valid = false;
            // }
            // if (!angular.isNumber(values.plot_nrows)) {
            //     errors.plot_nrows = true;
            //     valid = false;
            // }

            // if (errors !== {}) {
            //     return;
            // }

            $http.get('data/viewer/rows/',{
                'params': {
                    'db': AccountService.account.job.database,
                    'table': AccountService.account.job.table,
                    'cols': values.plot_x.name + ',' + values.plot_y.name,
                    'nrows': values.plot_nrows
                }
            }).success(function(response) {
                if (response.status == 'ok') {
                    var plot = {'x': [],'y': []};

                    for (var i=0; i<response.nrows; i++) {
                        plot.x.push(response.rows[i].cell[0]);
                        plot.y.push(response.rows[i].cell[1]);
                    }

                    AccountService.account.job.plot = plot;

                } else {
                    console.log('Error: Unknown response.');
                }
            }).error(function () {
                console.log('Error: Could not connect to server.');
            });
        }
    };
}]);

app.factory('DownloadService', ['$http','AccountService',function($http,AccountService) {
    var values = {};
    var errors = {};

    return {
        values: values,
        errors: errors,
        downloadTable: function() {
            var data = {};
            data = {
                'download_csrf': $('#download_csrf').attr('value'),
                'download_tablename': AccountService.account.job.table
            };

            // merge with form values
            angular.extend(data,values);

            $http.post('query/download/',$.param(data)).success(function(response) {
                if (response.status == 'ok') {
                    AccountService.account.job.download = {
                        'link': response.link,
                        'format': response.format
                    }
                } else if (response.status == 'error') {
                    errors[formName] = {};
                    angular.forEach(response.status, function(object, key) {
                        errors[key] = object;
                    });
                } else {
                    errors[formName] = {
                        'form': ['Unknown response.']
                    };
                }
            }).error(function () {
                errors[formName] = {
                    'form': ['Could not connect to server.']
                };
            });
        },
        regenerateTable: function() {
            var data = {};
            data = {
                'download_csrf': $('#download_csrf').attr('value'),
                'download_tablename': AccountService.account.job.table,
                'download_format': AccountService.account.job.download.format
            };

            $http.post('query/download/regenerate/',$.param(data)).success(function(response) {
                if (response.status == 'ok') {
                    AccountService.account.job.download = {
                        'link': response.link,
                        'format': response.format
                    }
                } else if (response.status == 'error') {
                    errors[formName] = {};
                    angular.forEach(response.status, function(object, key) {
                        errors[key] = object;
                    });
                } else {
                    errors[formName] = {
                        'form': ['Unknown response.']
                    };
                }
            }).error(function () {
                errors[formName] = {
                    'form': ['Could not connect to server.']
                };
            });
        }
    };
}]);

app.controller('SidebarController',['$scope','AccountService',function($scope,AccountService) {

    $scope.account = AccountService.account;

    $scope.activateForm = function(formName) {
        AccountService.account.active.form = formName;
        AccountService.account.active.job = false;

        $('#form-tab-header a').tab('show');

        $scope.activateJob(1);
        $('#results-tab-header a').tab('show');
    };

    $scope.activateJob = function(jobId) {

        AccountService.fetchJob(jobId).then(function() {
            // codemirrorfy the query
            CodeMirror.runMode(AccountService.account.job.query,"text/x-mysql",angular.element('#overview-query')[0]);
        });

        // if a form was active, switch to job overview tab
        if (AccountService.account.active.form != false) {
            $('#overview-tab-header a').tab('show');
        }

        AccountService.account.active.form = false;
        AccountService.account.active.job = jobId;
    };

    AccountService.fetchAccount();
    $scope.activateForm(AccountService.account.active.form);
}]);

app.controller('TabsController',['$scope','AccountService',function($scope,AccountService) {
    $scope.account = AccountService.account;
}]);

app.controller('FormController',['$scope','AccountService','FormService',function($scope,AccountService,FormService) {

    $scope.values = FormService.values;
    $scope.errors = FormService.errors;

    $scope.submitQuery = function(formName) {
        FormService.submitQuery(formName);
    };

}]);

app.controller('ResultsController',['$scope','AccountService','TableService',function($scope,AccountService,TableService) {

    TableService.url.cols = '/data/viewer/cols?db=daiquiri_user_admin&table=test';
    TableService.url.rows = '/data/viewer/rows?db=daiquiri_user_admin&table=test';

    TableService.data.cols = AccountService.account.job.cols;

    TableService.init();
}]);

app.controller('PlotController',['$scope','PlotService',function($scope,PlotService) {

    $scope.values = PlotService.values;
    $scope.errors = PlotService.errors;
    $scope.values.plot_nrows = 100;
    $scope.values.plot_y = 2;

    $scope.createPlot = function(isValid) {
        if (isValid) {
            PlotService.createPlot();
        }
    };

}]);

app.controller('DownloadController',['$scope','DownloadService',function($scope,DownloadService) {

    $scope.values = DownloadService.values;
    $scope.errors = DownloadService.errors;

    $scope.downloadTable = function() {
        DownloadService.downloadTable();
    };

    $scope.regenerateTable = function() {
        DownloadService.regenerateTable();
    };

}]);