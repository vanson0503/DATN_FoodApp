@extends('../layout')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <nav>
        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse" style="z-index:-1 !important">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
                <a href="{{ route('product.create') }}" class="btn rounded-pill btn-success">
                    <div data-i18n="Analytics">Thêm sản phẩm</div>
                </a>
            </div>
            <!-- /Search -->
            <div class="navbar-nav flex-row align-items-center ms-auto">
                <div class="nav-item d-flex align-items-center">
                    <i class="bx bx-search fs-4 lh-0"></i>
                    <input type="text" id="searchInput" class="form-control border-0 shadow-none"
                        placeholder="Search..." aria-label="Search..." onkeyup="filterProducts()" />
                </div>
            </div>
        </div>
    </nav>

    <div class="card">
        <h5 class="card-header">Danh sách sản phẩm</h5>
        <div id="productTable" class="table-responsive">
            <table id="productTableBody" class="table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên</th>
                        <!--<th>Description</th>-->
                        <th>Calo</th>
                        <th>Số lượng</th>
                        <th>Gia</th>
                        <!--<th>Ingredient</th>-->
                        <th>Ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="productList">
                    <!-- Products will be displayed here -->
                </tbody>
            </table>
            <nav aria-label="Page navigation"  style="display: flex; justify-content: center;">
                <ul class="pagination" id="pagination">
                    <!-- Pagination items will be added here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    var baseEditUrl = "{{ route('product.edit', ['id']) }}";
    var currentPage = 1;
    var totalProducts = 0;
    var productsPerPage = 10; // Number of products per page
    var totalPages = 0;
    var searchKeyword = ''; // Variable to store the search keyword

    document.addEventListener("DOMContentLoaded", function () {
    fetchProducts();
});

function fetchProducts() {
    let apiUrl = BASE_API_URL + 'paginateproducts?page=' + currentPage + '&limit=' + productsPerPage;
    if (searchKeyword.trim() !== '') {
        apiUrl += '&keyword=' + searchKeyword;
    }

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data.data)) {
                // Assuming the API provides total count in the response
                totalPages = data.last_page;
                currentPage = data.current_page;
                displayProducts(data.data);
                updatePagination();
            } else {
                console.error('Unexpected response format:', data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}


function displayProducts(products) {
    const productList = document.getElementById('productList');
    productList.innerHTML = ''; // Clear previous products
    products.forEach(product => {
        const id = product.id;
        const row = document.createElement('tr');
        
        const idCell = document.createElement('td');
        idCell.textContent = product.id;

        const nameCell = document.createElement('td');
        nameCell.textContent = product.name;

        const caloCell = document.createElement('td');
        caloCell.textContent = product.calo;

        const quantityCell = document.createElement('td');
        quantityCell.textContent = product.quantity;

        const priceCell = document.createElement('td');
        priceCell.textContent = product.price;

        const imageCell = document.createElement('td');
        const image = document.createElement('img');
        let src = "";
        if (product.images && product.images.length > 0) {
            let imageUrl = product.images[0].imgurl;
            if (!imageUrl.startsWith('https://')) {
                imageUrl = "https://vanson.io.vn/food-api/storage/app/public/product_images/" + imageUrl;
            }
            src = imageUrl;
        }

        image.src = src;
        image.style.width = '100px';
        imageCell.appendChild(image);

        const actionsCell = document.createElement('td');
        const dropdownDiv = document.createElement('div');
        dropdownDiv.classList.add('dropdown');

        const dropdownToggleBtn = document.createElement('button');
        dropdownToggleBtn.type = 'button';
        dropdownToggleBtn.classList.add('btn', 'p-0', 'dropdown-toggle', 'hide-arrow');
        dropdownToggleBtn.setAttribute('data-bs-toggle', 'dropdown');
        dropdownToggleBtn.innerHTML = '<i class="bx bx-dots-vertical-rounded"></i>';

        const dropdownMenu = document.createElement('div');
        dropdownMenu.classList.add('dropdown-menu');

        const editLink = document.createElement('a');
        editLink.classList.add('dropdown-item');
        editLink.href = baseEditUrl.replace('id', id);
        editLink.innerHTML = '<i class="bx bx-edit-alt me-2"></i> Sửa';

        const deleteLink = document.createElement('a');
        deleteLink.classList.add('dropdown-item');
        deleteLink.href = 'javascript:void(0);';
        deleteLink.innerHTML = '<i class="bx bx-trash me-2"></i> Xóa';
        deleteLink.addEventListener('click', function () {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm?')) {
                fetch(BASE_API_URL + 'products/' + product.id, {
                    method: 'DELETE'
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 200 && status < 300) {
                        showToast("Xóa sản phẩm", body.message, "bg-success");
                        product.row.remove(); // Remove the row from the table
                    } else {
                        showToast("Xóa sản phẩm", body.message, "bg-danger");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product.');
                });
            }

        });

        dropdownMenu.appendChild(editLink);
        dropdownMenu.appendChild(deleteLink);

        dropdownDiv.appendChild(dropdownToggleBtn);
        dropdownDiv.appendChild(dropdownMenu);

        actionsCell.appendChild(dropdownDiv);

        product.row = row;

        row.appendChild(idCell);
        row.appendChild(nameCell);
        row.appendChild(caloCell);
        row.appendChild(quantityCell);
        row.appendChild(priceCell);
        row.appendChild(imageCell);
        row.appendChild(actionsCell);

        productList.appendChild(row);
    });
}

function updatePagination() {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = ''; // Clear previous pagination

    const firstPage = document.createElement('li');
    firstPage.classList.add('page-item');
    firstPage.innerHTML = '<a class="page-link" href="javascript:void(0);" onclick="changePage(1)"><i class="tf-icon bx bx-chevrons-left"></i></a>';
    pagination.appendChild(firstPage);

    const prevPage = document.createElement('li');
    prevPage.classList.add('page-item');
    prevPage.innerHTML = '<a class="page-link" href="javascript:void(0);" onclick="changePage(currentPage - 1)"><i class="tf-icon bx bx-chevron-left"></i></a>';
    pagination.appendChild(prevPage);

    for (let i = 1; i <= totalPages; i++) {
        const pageItem = document.createElement('li');
        pageItem.classList.add('page-item');
        if (i === currentPage) {
            pageItem.classList.add('active');
        }
        pageItem.innerHTML = `<a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>`;
        pagination.appendChild(pageItem);
    }

    const nextPage = document.createElement('li');
    nextPage.classList.add('page-item');
    nextPage.innerHTML = '<a class="page-link" href="javascript:void(0);" onclick="changePage(currentPage + 1)"><i class="tf-icon bx bx-chevron-right"></i></a>';
    pagination.appendChild(nextPage);

    const lastPage = document.createElement('li');
    lastPage.classList.add('page-item');
    lastPage.innerHTML = '<a class="page-link" href="javascript:void(0);" onclick="changePage(totalPages)"><i class="tf-icon bx bx-chevrons-right"></i></a>';
    pagination.appendChild(lastPage);

    if (currentPage === 1) {
        prevPage.classList.add('disabled');
        firstPage.classList.add('disabled');
    }
    if (currentPage === totalPages) {
        nextPage.classList.add('disabled');
        lastPage.classList.add('disabled');
    }
}

function changePage(page) {
    if (page < 1) {
        page = 1;
    }
    if (page > totalPages) {
        page = totalPages;
    }
    currentPage = page;
    fetchProducts();
}

let typingTimer; 
const doneTypingInterval = 500;

function filterProducts() {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(performSearch, doneTypingInterval);
}

function performSearch() {
    const input = document.getElementById('searchInput');
    searchKeyword = input.value.trim();
    currentPage = 1; // Reset current page to 1 when performing a new search
    fetchProducts();
}

</script>
@endsection
