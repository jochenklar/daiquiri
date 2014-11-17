/*  
 *  Copyright (c) 2012-2014 Jochen S. Klar <jklar@aip.de>,
 *                           Adrian M. Partl <apartl@aip.de>, 
 *                           AIP E-Science (www.aip.de)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

angular.module('table', ['ngSanitize'])

.filter('ucfirst', function() {
    return function(input,arg) {
        return input.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    };
})

.directive('daiquiriTable', ['TableService',function(TableService) {
    return {
        templateUrl: '/daiquiri/html/table.html',
        link: {
            pre: function (scope, element, attrs) {
                // look for the cols url in the attributes
                if (!angular.isUndefined(attrs.cols)) {
                    TableService.url.cols = attrs.cols;
                }

                // look for the rows url in the attributes
                if (!angular.isUndefined(attrs.rows)) {
                    TableService.url.rows = attrs.rows;
                }

                // watch the cols for a change, and perform callback
                if (angular.isFunction(TableService.callback.cols)) {
                    scope.$watch(function () {
                        return TableService.trigger.cols;
                    }, function(newValue, oldValue) {
                        scope.$evalAsync(function($scope) {
                            TableService.callback.cols($scope);
                        });
                    }, true);
                }

                // watch the rows for a change, and perform callback
                if (angular.isFunction(TableService.callback.rows)) {
                    scope.$watch(function () {
                        return TableService.trigger.rows;
                    }, function(newValue, oldValue) {
                        scope.$evalAsync(function($scope) {
                            TableService.callback.rows($scope);
                        });
                    }, true);
                }

                // init the table
                TableService.init();
            }
        }
    };
}])

.factory('TableService', ['$http',function($http) {
    
    var url = {
        cols: null,
        rows: null
    };

    var data = {
        cols: [],
        rows: []
    };

    var meta = {
        nrows: null,
        page: null,
        pages: null,
        total: null,
        sorted: null
    };

    var params = {
        'nrows': 10,
        'page': 1,
        'sort': null,
        'search': null
    };

    var callback = {
        'rows': null,
        'cols': null
    };

    var trigger = {
        'rows': false,
        'cols': false
    };

    function first() {
        if (params.page != 1) {
            params.page = 1;
            fetchRows();
        }
    }

    function prev() {
        if (params.page > 1) {
            params.page -= 1;
            fetchRows();
        }
    }

    function next() {
        if (params.page < meta.pages) {
            params.page += 1;
            fetchRows();
        }
    }

    function last() {
        if (params.page != meta.pages) {
            params.page = meta.pages;
            fetchRows();
        }
    }

    function reset() {
        params.page = 1;
        params.sort = null;
        params.search = null;
        fetchRows();
    }

    function search(searchString) {
        params.page = 1;
        params.sort = null;
        params.search = searchString;
        fetchRows();
    }

    function sort(colname) {
        if (params.sort == colname + ' ASC') {
            params.sort = colname + ' DESC';
        } else {
            params.sort = colname + ' ASC';
        }

        meta.sort = params.sort;

        fetchRows();
    }

    function changeNRows(nrows) {
        params.nrows = nrows;
        reset();
    }

    function fetchCols() {
        if (url.cols !== null) {
            $http.get(url.cols,{'params': params}).success(function(response) {
                if (response.status == 'ok') {
                    data.cols = response.cols;
                    trigger.cols = !trigger.cols;
                } else {
                    console.log('Error');
                }
            });
        }
    }

    function fetchRows() {
        if (url.cols !== null) {
            $http.get(url.rows,{'params': params}).success(function(response) {
                if (response.status == 'ok') {
                    data.rows = response.rows;
                    meta.nrows = response.nrows;
                    meta.page = response.page;
                    meta.pages = response.pages;
                    meta.total = response.total;
                    trigger.rows = !trigger.rows;
                } else {
                    console.log('Error');
                }
            });
        }
    }

    function init() {
        // fetch cols if they where not loaded before
        if (data.cols.length === 0) fetchCols();

        // fetch cols
        fetchRows();
    }

    return {
        url: url,
        data: data,
        meta: meta,
        callback: callback,
        trigger: trigger,
        first: first,
        prev: prev,
        next: next,
        last: last,
        reset: reset,
        search: search,
        sort: sort,
        changeNRows: changeNRows,
        fetchCols: fetchCols,
        fetchRows: fetchRows,
        init: init
    };
}])

.controller('TableController', ['$scope','$document','TableService',function($scope,$document,TableService) {

    $scope.table = {
        'data': TableService.data,
        'meta': TableService.meta,
        'nrows': 10,
        'options': [
            {'name': 'Show 10 rows', 'value': 10},
            {'name': 'Show 20 rows', 'value': 20},
            {'name': 'Show 100 rows', 'value': 100}
        ]
    };

    $scope.first = function() {
        TableService.first();
    };

    $scope.prev = function() {
        TableService.prev();
    };

    $scope.next = function() {
        TableService.next();
    };

    $scope.last = function() {
        TableService.last();
    };

    $scope.reset = function() {
        TableService.reset();
    };

    $scope.search = function() {
        TableService.search($scope.searchString);
    };

    $scope.sort = function(col) {
        TableService.sort(col);
    };

    $scope.changeNRows = function() {
        TableService.changeNRows($scope.nrows);
    };

    $scope.resize = function (colId) {
        var zero = event.pageX;
        var table = angular.element('.daiquiri-table table');
        var th = angular.element('[data-col-id="' + colId + '"]');
        var width = th.width();

        table.addClass('no-select');
        function enterResize(event) {
            var newWidth = width + event.pageX - zero;
            if (newWidth >= 40) th.width(newWidth);
        }

        function exitResize() {
            table.removeClass('no-select');
            $document.off('mousemove',enterResize);
            $document.off('mouseup',exitResize);
        }

        $document.on('mousemove', enterResize);
        $document.on('mouseup', exitResize);
    };

}]);
