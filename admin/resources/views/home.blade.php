@extends('../layout')

@section('content')
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
    }

    .date-picker-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .date-picker-container label {
        margin-right: 10px;
    }

    .date-picker-container input {
        margin-right: 20px;
        padding: 5px;
    }

    #result {
        font-size: 1.2em;
        color: #007bff;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Xử lý đơn hàng 📦</h5>
                            <p class="mb-4">
                                Bạn có <span id="pendingOrders" class="fw-bold">0</span> đơn hàng cần xửa lý.
                            </p>
                            <a href="{{ route('confirm') }}" class="btn btn-sm btn-outline-primary">Xem đơn hàng</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset("assets/img/illustrations/add-product.png") }}" height="160"
                                alt="Order Processing" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script>
            document.addEventListener('DOMContentLoaded', function () {
                fetch(BASE_API_URL + "stats/order-status")
                    .then(response => response.json())
                    .then(data => {
                        const pendingOrders = data.find(order => order.status === 'initialization');
                        const pendingOrdersCount = pendingOrders ? pendingOrders.total_orders : 0;
                        document.getElementById('pendingOrders').textContent = pendingOrdersCount;
                    })
                    .catch(error => console.error('Error fetching pending orders:', error));
            });
        </script>
        <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{asset("assets/img/icons/unicons/chart-success.png")}}"
                                        alt="chart success" class="rounded" />
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Doanh thu với tháng trước</span>
                            <h3 id="profitAmount" class="card-title mb-2">0đ</h3>
                            <small id="profitChange" class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i>
                                0%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                fetch(BASE_API_URL + "stats/monthly-revenue-with-change")
                    .then(response => response.json())
                    .then(data => {
                        const latestMonthData = data[data.length - 1]; // Get the latest month data
                        const profitAmountElement = document.getElementById('profitAmount');
                        const profitChangeElement = document.getElementById('profitChange');

                        const profitAmount = parseFloat(latestMonthData.revenue); // Ensure the revenue is a number
                        const profitChange = latestMonthData.change;

                        // Format the revenue as VND
                        const formattedProfitAmount = new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(profitAmount);

                        profitAmountElement.textContent = formattedProfitAmount;

                        if (profitChange >= 0) {
                            profitChangeElement.innerHTML = `<i class="bx bx-up-arrow-alt"></i> ${profitChange.toFixed(2)}%`;
                            profitChangeElement.classList.add('text-success');
                            profitChangeElement.classList.remove('text-danger');
                        } else {
                            profitChangeElement.innerHTML = `<i class="bx bx-down-arrow-alt"></i> ${profitChange.toFixed(2)}%`;
                            profitChangeElement.classList.add('text-danger');
                            profitChangeElement.classList.remove('text-success');
                        }
                    })
                    .catch(error => console.error('Error fetching profit data:', error));
            });
        </script>
        <!-- Total Revenue -->

    </div>
    <div class="row">
        <div class="col-lg-8 col-md-4 order-1 h-100">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body row align-items-center">
                            <label for="html5-date-input-start" class="col-md-3 col-form-label">Thống kê</label>
                            <div class="col-md-3">
                                <input class="form-control" type="date" id="html5-date-input-start" />
                            </div>
                            <label for="html5-date-input-end" class="col-md-1 col-form-label text-center"> đến </label>
                            <div class="col-md-3">
                                <input class="form-control" type="date" id="html5-date-input-end" />
                            </div>
                            <div class="col-md-2">
                                <button id="fetch-data-button" onclick="fetchDataByDay()"
                                    class="btn btn-primary">Lọc</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Get today's date
                var today = new Date();

                // Get the date one month before today
                var lastMonth = new Date();
                lastMonth.setMonth(today.getMonth() - 1);

                // Format the dates to YYYY-MM-DD
                var todayStr = today.toISOString().split('T')[0];
                var lastMonthStr = lastMonth.toISOString().split('T')[0];

                // Set the input values
                document.getElementById('html5-date-input-start').value = lastMonthStr;
                document.getElementById('html5-date-input-end').value = todayStr;
                fetchDataByDay();
            });


            function fetchDataByDay() {
                var startDateInput = document.getElementById('html5-date-input-start').value;
                var endDateInput = document.getElementById('html5-date-input-end').value;

                var currentDate = new Date();
                var startDate = new Date(startDateInput);
                var endDate = new Date(endDateInput);

                if (startDate > currentDate || endDate > currentDate) {
                    showToast('Cảnh báo', 'Ngày không được lớn hơn ngày hôm nay!', 'bg-warning');
                    return;
                }

                if (startDate > endDate) {
                    showToast('Cảnh báo', 'Vui lòng chọn ngày sau lớn hơn ngày trước!', 'bg-warning');
                    return;
                }

                var startDate = formatDate(startDateInput);
                var endDate = formatDate(endDateInput);

                fetch(BASE_API_URL + `stats/daily-revenue?start_date=${startDate}&end_date=${endDate}`)
                    .then(response => response.json())
                    .then(data => {
                        var dates = data.map(item => item.day);
                        var revenues = data.map(item => item.revenue);

                        displayChart(dates, revenues);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            function formatDate(dateStr) {
                var date = new Date(dateStr);
                var year = date.getFullYear();
                var month = ('0' + (date.getMonth() + 1)).slice(-2); // Add leading zero
                var day = ('0' + date.getDate()).slice(-2); // Add leading zero
                return `${year}-${month}-${day}`;
            }

            function displayChart(dates, revenues) {
                var ctx = document.getElementById('revenueChart').getContext('2d');
                if (window.myChart !== undefined) {
                    window.myChart.destroy(); // Destroy the existing Chart instance
                }
                window.myChart = new Chart(ctx, {
                    type: 'bar', // Change type to 'bar' for bar chart
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Doanh thu theo ngày',
                            data: revenues,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)', // Background color of the bars
                            borderColor: 'rgba(75, 192, 192, 1)', // Border color of the bars
                            borderWidth: 1, // Border width of the bars
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'dd-MM-yyyy',
                                    displayFormats: {
                                        day: 'dd-MM-yyyy'
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Ngày'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Doanh thu'
                                },
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return `Doanh thu: $${context.raw.toFixed(2)}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        </script>


        <!--/ Total Revenue -->
        <div class="col-lg-4 col-md-4 order-1 h-100">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body align-items-center">
                            <canvas id="productCategorySalesChart" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-4 order-1">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Số sản phẩm bán theo danh mục</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div id="soldProductStatisticsChart"></div>
                    </div>
                    <ul class="p-0 m-0" id="soldCategoryList">
                        <!-- Category items will be injected here -->
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-4 order-1">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Doanh thu theo tháng</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <canvas id="weeklyRevenueChart" width="1000" height="600"></canvas>
                    </div>

                </div>
            </div>
        </div>

        <script>
            async function fetchSoldProductCountByCategoryWithImage() {
                try {
                    const response = await fetch(BASE_API_URL + 'stats/sold-product-count-by-category');
                    const data = await response.json();

                    const soldCategoryList = document.getElementById('soldCategoryList');
                    soldCategoryList.innerHTML = '';

                    data.forEach(item => {
                        const listItem = document.createElement('li');
                        listItem.classList.add('d-flex', 'mb-4', 'pb-1');

                        listItem.innerHTML = `
                        <div class="avatar flex-shrink-0 me-3">
                            <img src="https://vanson.io.vn/food-api/storage/app/public/category_images/${item.image_url}" alt="${item.name}" class="rounded" style="width: 40px; height: 40px;">
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">${item.name}</h6>
                            </div>
                            <div class="user-progress">
                                <small class="fw-semibold">Đã bán ${item.sold_count}</small>
                            </div>
                        </div>
                    `;
                        soldCategoryList.appendChild(listItem);
                    });

                } catch (error) {
                    console.error('Error fetching sold product count by category with image:', error);
                }
            }

            document.addEventListener('DOMContentLoaded', fetchSoldProductCountByCategoryWithImage);
        </script>

    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('weeklyRevenueChart').getContext('2d');

            // Fetch month revenue data from API
            fetch(BASE_API_URL + 'stats/monthly-revenue-with-change')
                .then(response => response.json())
                .then(data => {
                    const labels = data.map(item => `Tháng ${item.month}`);
                    const revenues = data.map(item => item.revenue);

                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Doanh thu theo tháng',
                                data: revenues,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: false,
                            }]
                        },
                        options: {
                            scales: {
                                x: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Tháng'
                                    }
                                },
                                y: {
                                    display: true,
                                    title: {
                                        display: true,
                                        text: 'Doanh thu'
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('productCategorySalesChart').getContext('2d');

            // Fetch product category sales data from API
            fetch(BASE_API_URL + 'stats/category-sales')
                .then(response => response.json())
                .then(data => {
                    const labels = data.map(item => item.name);
                    const sales = data.map(item => item.quantity_sold);

                    const chart = new Chart(ctx, {
                        type: 'polarArea',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Số sản phẩm bán theo danh mục',
                                data: sales,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.5)',
                                    'rgba(54, 162, 235, 0.5)',
                                    'rgba(255, 206, 86, 0.5)',
                                    'rgba(75, 192, 192, 0.5)',
                                    'rgba(153, 102, 255, 0.5)',
                                    'rgba(255, 159, 64, 0.5)'
                                ],
                                borderWidth: 1
                            }]
                        }
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });
    </script>

    @endsection