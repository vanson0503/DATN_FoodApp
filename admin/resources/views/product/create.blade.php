@extends('../layout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h1>Thêm sản phẩm</h1>
    <form id="productForm" method="POST" enctype="multipart/form-data" class="product-form">
        <label for="name" class="form-label">Tên sản phẩm:</label><br>
        <input type="text" id="name" name="name" class="form-control"><br><br>

        <label for="description" class="form-label">Mô tả:</label><br>
        <textarea id="description" name="description" class="form-control"></textarea><br><br>

        <label for="ingredient" class="form-label">Nguyên liệu:</label><br>
        <textarea id="ingredient" name="ingredient" class="form-control"></textarea><br><br>

        <label for="calo" class="form-label">Calo:</label><br>
        <input type="number" id="calo" name="calo" class="form-control"><br><br>

        <label for="quantity" class="form-label">Số lượng:</label><br>
        <input type="number" id="quantity" name="quantity" class="form-control"><br><br>

        <label for="price" class="form-label">Giá:</label><br>
        <input type="number" id="price" name="price" class="form-control"><br><br>
        
        <label for="discount" class="form-label">Giảm giá:</label><br>
        <input type="number" id="discount" name="discount" class="form-control" min="0" max="100" value="0"><br><br>

        <label for="categories" class="form-label">Danh mục:</label><br>
        <div id="categoriesContainer" class="category-container"></div><br>

        <label for="image" class="form-label">Ảnh:</label><br>
        <input type="file" id="images" name="images[]" multiple class="form-control"><br><br>

        <input type="submit" value="Submit" class="btn btn-primary">
    </form>
</div>

<script>
    // Fetch categories from API
    fetch(BASE_API_URL + 'category')
        .then(response => response.json())
        .then(categories => {
            const categoriesContainer = document.getElementById('categoriesContainer');
            categories.forEach(category => {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'category[]';
                checkbox.value = category.id;

                const label = document.createElement('label');
                label.htmlFor = 'category' + category.id;
                label.textContent = category.name;

                categoriesContainer.appendChild(checkbox);
                categoriesContainer.appendChild(label);
                categoriesContainer.appendChild(document.createElement('br'));
            });
        });

    document.getElementById('productForm').addEventListener('submit', function (event) {
        event.preventDefault();
        if (validateForm()) {
            const formData = new FormData(this);
            fetch(BASE_API_URL + 'products', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    } else {
                        throw new Error('Network response was not ok.');
                    }
                })
                .then(htmlData => {
                    // Show success toast
                    showToast('Success', 'Product added successfully', 'bg-success');
                    console.log(htmlData); // In ra dữ liệu HTML
                    document.getElementById('productForm').reset();
                })
                .catch(error => {
                    // Show error toast
                    showToast('Error', 'Failed to add product', 'bg-danger');
                    console.error('Error:', error);
                });
        }
    });

    function validateForm() {
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();
        const ingredient = document.getElementById('ingredient').value.trim();
        const calo = document.getElementById('calo').value.trim();
        const quantity = document.getElementById('quantity').value.trim();
        const price = document.getElementById('price').value.trim();
        const discount = document.getElementById('discount').value.trim();

        if (!name) {
            showToast('Error', 'Tên sản phẩm không được để trống', 'bg-danger');
            return false;
        }
        if (!description) {
            showToast('Error', 'Mô tả không được để trống', 'bg-danger');
            return false;
        }
        if (!ingredient) {
            showToast('Error', 'Nguyên liệu không được để trống', 'bg-danger');
            return false;
        }
        if (!calo || !isPositiveNumber(calo)) {
            showToast('Error', 'Calo phải là số dương', 'bg-danger');
            return false;
        }
        if (!quantity || !isPositiveNumber(quantity)) {
            showToast('Error', 'Số lượng phải là số dương', 'bg-danger');
            return false;
        }
        if (!price || !isPositiveNumber(price)) {
            showToast('Error', 'Giá phải là số dương', 'bg-danger');
            return false;
        }
        if (!discount || !isValidDiscount(discount)) {
            showToast('Error', 'Giảm giá phải từ 0 đến 100', 'bg-danger');
            return false;
        }
        return true;
    }

    function isPositiveNumber(value) {
        return value > 0;
    }

    function isValidDiscount(value) {
        return value >= 0 && value <= 100;
    }

    // Function to show toast dynamically
    function showToast(title, message, className) {
        // Create the toast element
        const toast = document.createElement('div');
        toast.className = `bs-toast toast toast-placement-ex m-2 fade ${className} top-0 start-50 translate-middle-x show`;
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        // Append the toast to the container
        document.body.appendChild(toast);

        // Remove the toast after a delay
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 150);
        }, 3000);
    }
</script>
@endsection
