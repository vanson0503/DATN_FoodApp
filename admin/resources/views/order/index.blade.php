@extends('../layout')

@section('content')
<style>
    nav {
        display: flex;
        justify-content: center;
        /* Căn giữa theo chiều ngang */
        align-items: center;
        /* Căn giữa theo chiều dọc, nếu cần */
    }

    .table-min-height {
        min-height: 100px;
        /* This will not directly apply as expected due to table behavior */
        empty-cells: show;
    }

    .table-min-height tr,
    .table-min-height td {
        height: 100px;
        /* This ensures that at least the row will maintain height */
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="container my-4 table-responsive">
        <h1 class="mb-4 text-center">Danh sách đơn hàng</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <!-- <th>Customer ID</th> -->
                    <th>Tên khách hàng</th>
                    <!-- <th>Phone Number</th>
                <th>Address</th>
                <th>Note</th> -->
                    <th>Phương thức thanh toán</th>
                    <th>Tình trạng đơn hàng</th>
                    <th>Thời gian tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <!-- Orders will be populated here by JavaScript -->
            </tbody>
        </table>
        <nav aria-label="Page navigation" style="display: flex; justify-content: center;">
            <ul class="pagination" id="pagination"></ul>
        </nav>


    </div>



    <div class="modal fade" id="exLargeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel4">Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="detailInfo">
                    </div>
                    <div id="productList">
                        <h5 class="card-header">Product list</h5>
                        <div class="table-responsive text-nowrap">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0" id="productDetailsBody" style="min-height:1000px">
                                    <!-- Product rows will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    function getBaseUrl() {
        const { protocol, hostname, port } = window.location;
        return `${protocol}//${hostname}${port ? ':' + port : ''}`;
    }

    const baseUrl = getBaseUrl();
    function translatePayment(payment) {
        const translations = {
            'online': 'Online',
            'cash': 'Tiền mặt'
        };
        return translations[payment] || payment;
    }

    function translatePaymentStatus(status) {
        const translations = {
            'initialization': 'Khởi tạo',
            'completed': 'Hoàn thành',
            'failed': 'Thất bại'
        };
        return translations[status] || status;
    }

    function translateStatus(status) {
        const translations = {
            'initialization': 'Đang chờ xử lý',
            'confirm': 'Xác nhận',
            'delivering': 'Đang giao hàng',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy',
            'refund': 'Hoàn hàng'
        };
        return translations[status] || status;
    }

    function displayOrderDetails(order) {
        const detailInfo = document.getElementById('detailInfo');
        const productList = document.getElementById('productDetailsBody');
        productList.innerHTML = "";

        let html = `
        <h2>Chi tiết đơn hàng: #${order.id}</h2>
        <p><strong>Tên người nhận:</strong> ${order.name}</p>
        <p><strong>Số điện thoại:</strong> ${order.phone_number}</p>
        <p><strong>Địa chỉ:</strong> ${order.address}</p>
        <p><strong>Phương thức thanh toán:</strong> ${translatePayment(order.payment)}, Trạng thái: ${translatePaymentStatus(order.payment_status)}</p>
        <p><strong>Tình trạng đơn hàng:</strong> ${translateStatus(order.status)}</p>
        <p><strong>Thời gian tạo đơn:</strong> ${order.created_time}</p>
        <p><strong>Ghi chú:</strong> ${order.note || 'None'}</p>
        `;

        detailInfo.innerHTML = html;

        order.details.forEach(item => {
            var tr = document.createElement('tr');
            tr.innerHTML = `
            <td><strong>${item.product.name}</strong></td>
            <td>${item.quantity}</td>
            <td>$${item.price}</td>
            <td>$${(parseFloat(item.price) * item.quantity).toFixed(2)}</td>
        `;
            productList.appendChild(tr);
        });

    }

    function orderDetail(orderId) {
        fetch(BASE_API_URL + "orderdetail/" + orderId)
            .then(response => response.json())
            .then(data => {
                displayOrderDetails(data);
            })
            .catch(error => console.error('Error loading the order:', error));
    }

    function populateTable(orders) {
        const tableBody = document.getElementById('orderTableBody');
        tableBody.innerHTML = ''; // Clear current rows
        orders.forEach(order => {
            const dateTime = new Date(order.created_time);

            // Lấy ngày, tháng và năm
            const year = dateTime.getFullYear();
            const month = dateTime.getMonth() + 1; // Tháng bắt đầu từ 0 nên cần cộng thêm 1
            const date = dateTime.getDate();

            // Định dạng lại thành chuỗi ngày tháng năm
            const formattedDate = `${date}/${month}/${year}`;
            const row = `
            <tr>
                <td>${order.id}</td>
                <td>${order.name}</td>
                <td>${translatePaymentStatus(order.payment_status)}</td>
                <td>${translateStatus(order.status)}</td>
                <td>${formattedDate}</td>
                <td>
                <i data-bs-toggle="modal" data-bs-target="#exLargeModal" title="Order detail" class='bx bx-detail' style='cursor: pointer;' onclick="orderDetail(${order.id})"></i>
                ${renderActionButtons(order)}
                </td>
            </tr>
        `;
            tableBody.innerHTML += row;
        });
    }
    function updateOrderStatus(orderId, status) {
        fetch(BASE_API_URL+`updateorderstatus`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: orderId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            showToast("Đơn hàng","Cập nhật trạng thái thành công!",'bg-success')
            loadData();
        })
        .catch(error => {
            showToast("Đơn hàng","Cập nhật trạng thái đơn hàng thất bại!",'bg-danger')
            console.error('Error updating order status:', error)
        });
    }

    function renderActionButtons(order) {
        const status = order.status;
        const orderId = order.id;
        const actionBtns = document.createElement('td');

        if (status === 'initialization') {
            actionBtns.innerHTML = `
            <button class="btn btn-primary" onclick="updateOrderStatus(${orderId}, 'confirm')">Xác nhận</button>
        `;
        } else if (status === 'confirm') {
            actionBtns.innerHTML = `
            <button class="btn btn-success" onclick="updateOrderStatus(${orderId}, 'delivering')">Giao hàng</button>
        `;
        }
        if (status !== 'cancelled' && status !== 'refund'&&status !== 'completed') {
            actionBtns.innerHTML += `
            <button class="btn btn-danger" onclick="updateOrderStatus(${orderId}, 'cancelled')">Hủy</button>
        `;
        }

        return actionBtns.outerHTML;
    }

    function setupPagination(data) {
        let currentPage = data.current_page;
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = ''; // Clear existing pagination buttons

        const totalPages = data.total_pages;

        // Helper function to create a page item
        function createPageItem(page, isActive = false) {
            const pageUrl = baseUrl + `/admin/public/order?page=${page}`;
            return `<li class="page-item ${isActive ? 'active' : ''}">
                    <a class="page-link" href="${pageUrl}">${page}</a>
                </li>`;
        }

        // Adding first page and previous
        if (currentPage > 1) {
            const firstPageUrl = baseUrl + `/admin/public/order?page=1`;
            const prevPageUrl = baseUrl + `/admin/public/order?page=${currentPage - 1}`;
            pagination.innerHTML += `
            <li class="page-item first">
                <a class="page-link" href="${firstPageUrl}">
                    <i class="tf-icon bx bx-chevrons-left"></i>
                </a>
            </li>
            <li class="page-item prev">
                <a class="page-link" href="${prevPageUrl}">
                    <i class="tf-icon bx bx-chevron-left"></i>
                </a>
            </li>
        `;
        }

        // Page numbers: showing only 5 pages around the current page
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        for (let page = startPage; page <= endPage; page++) {
            pagination.innerHTML += createPageItem(page, page == currentPage);
        }
        if (currentPage < totalPages) {
            const nextPageUrl = baseUrl + `/admin/public/order?page=${Number(currentPage) + 1}`;
            const lastPageUrl = baseUrl + `/admin/public/order?page=${totalPages}`;
            pagination.innerHTML += `
            <li class="page-item next">
                <a class="page-link " href="${nextPageUrl}">
                    <i class="bx bx-chevron-right"></i>
                </a>
            </li>
            <li class="page-item last">
                <a class="page-link " href="${lastPageUrl}">
                    <i class="bx bx-chevrons-right"></i>
                </a>
            </li>
        `;
        }
    }

    function loadData(){
        const urlParams = new URLSearchParams(window.location.search);
        let currentPage = parseInt(urlParams.get('page'), 10) || 1;

        const baseUrl = BASE_API_URL + 'orders?page=' + currentPage;
        fetchOrders(baseUrl);

        function fetchOrders(url) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    populateTable(data.data);
                    setupPagination(data);
                })
                .catch(error => console.error('Error loading the orders:', error));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadData()
    });
</script>
@endsection