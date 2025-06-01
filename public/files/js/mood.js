(function () {
    angular
      .module('calendarApp', ['ngAnimate'])
      .controller('calendarController', calendarController);

    function calendarController($scope, $http) {
        var vm = this;
        var now = new Date();
        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var month = now.getMonth();
        var year = now.getFullYear();
        var monthDay = daysInMonth(month + 1, year);
        var n = now.getDate();
        var uidi, uidm, uid;

        vm.id = n.toString() + month.toString();
        vm.dataId;
        vm.events = [];
        vm.description;
        vm.type = '😢';
        vm.month = months[month];
        vm.next = next;
        vm.prev = prev;
        vm.add = add;
        vm.toggleCompleted = toggleCompleted;

        // 页面加载时从数据库加载现有数据
        loadMoodData();

        vm.getStats = function () {
            var currentDate = parseInt(vm.id.slice(0, -1));
            var currentMonth = parseInt(vm.id.slice(-1));
            var stats = {
                '😢': 0,
                '😡': 0,
                '😌': 0,
                '😄': 0,
                '😐': 0
            };
            for (var i = 0; i < vm.events.length; i++) {
                var eventDate = parseInt(vm.events[i].id.slice(0, -1));
                var eventMonth = parseInt(vm.events[i].id.slice(-1));
                if (eventMonth === currentMonth && eventDate >= currentDate - 6 && eventDate <= currentDate) {
                    stats[vm.events[i].type]++;
                }
            }
            return stats;
        };

        // 加载情绪数据
        function loadMoodData() {
            $http.get('/MindHeaven/public/files/php/load_mood.php') // 修改为绝对路径
                .then(function(response) {
                    if (response.data.success) {
                        vm.events = response.data.events || [];
                        console.log("Data loaded successfully:", vm.events);
                    } else {
                        console.error("Error loading data:", response.data.error);
                    }
                })
                .catch(function(error) {
                    console.error("Error loading data:", error);
                });
        }

        // 保存情绪数据
        function saveMoodData(data) {
            $http.post('/MindHeaven/public/files/php/save_mood.php', data) // 修改为绝对路径
                .then(function(response) {
                    if (response.data.success) {
                        console.log("Data saved successfully:", response.data);
                    } else {
                        console.error("Error saving data:", response.data.error);
                    }
                })
                .catch(function(error) {
                    console.error("Error saving data:", error);
                });
        }
        
        function placeIt() {
            var firstDay = new Date(year, month, 1).getDay();
            var marginLeft = firstDay === 0 ? 6 * 50 : (firstDay - 1) * 50;
            $(".date_item").first().css({
                'margin-left': marginLeft + 'px'
            });
        }

        function presentDay() {
            $(".date_item").eq(n - 1).addClass("present");
        }

        function showDays(days) {
            for (var i = 1; i < days; i++) {
                var uidi = i;
                var uidm = month;
                var uid = uidi.toString() + uidm.toString();
                $(".dates").append("<div class='date_item' data='" + uid + "'>" + i + "</div>");
            }
        }

        function daysInMonth(month, year) {
            return new Date(year, month, 0).getDate() + 1;
        }

        function next() {
            if (month < 11) {
                month++;
            } else {
                month = 0;
                year++;
            }
            $(".dates").html('');
            vm.month = months[month];
            monthDay = daysInMonth(month + 1, year);
            showDays(monthDay);
            placeIt();
        }

        function prev() {
            if (month === 0) {
                month = 11;
                year--;
            } else {
                month--;
            }
            $(".dates").html('');
            vm.month = months[month];
            monthDay = daysInMonth(month + 1, year);
            showDays(monthDay);
            placeIt();
        }
        
        function add() {
            if (!vm.description || vm.description.trim() === '') {
                alert('请输入情绪描述');
                return;
            }

            var newEvent = {
                id: vm.id,
                description: vm.description.trim(),
                type: vm.type,
                completed: false
            };

            // 检查是否已存在相同日期的记录
            var existingEventIndex = -1;
            for (var i = 0; i < vm.events.length; i++) {
                if (vm.events[i].id === vm.id) {
                    existingEventIndex = i;
                    break;
                }
            }

            if (existingEventIndex !== -1) {
                // 更新现有记录
                vm.events[existingEventIndex] = newEvent;
            } else {
                // 添加新记录
                vm.events.push(newEvent);
            }
            
            // 保存到数据库
            saveMoodData({
                date: vm.id,
                type: vm.type,
                description: vm.description.trim(),
                completed: false
            });
            
            vm.description = "";
        }
        
        function toggleCompleted(event) {
            event.completed = !event.completed;
            // 更新数据库中的完成状态
            saveMoodData({
                date: event.id,
                type: event.type,
                description: event.description,
                completed: event.completed
            });
        }
        
        $(".dates").on("click", ".date_item", function () {
            vm.id = $(this).attr('data');
            vm.dataId = $(this).attr('data');
            $(this).addClass("present").siblings().removeClass("present");
            $scope.$apply();
        });

        showDays(monthDay);
        presentDay();
        placeIt();
    }
})();