@extends('../layout')

@section('content')
<style>
    .imageContainer {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-right: 10px;
        position: relative;
    }

    .deleteIcon {
        cursor: pointer;
        position: absolute;
        top: 0;
        right: 0;
        transform: translate(50%, -50%);
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<div class="container-xxl flex-grow-1 container-p-y">
    <h1>Cập nhật sản phẩm</h1>
    <form id="updateProductForm" enctype="multipart/form-data" class="product-form">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" required><br>

        <label for="description" class="form-label">Mô tả:</label><br>
        <textarea id="description" name="description" class="form-control" rows="4" cols="50" required></textarea><br>

        <label for="ingredient" class="form-label">Nguyên liệu:</label><br>
        <textarea id="ingredient" name="ingredient" class="form-control"></textarea><br><br>

        <label for="calo" class="form-label">Calo:</label>
        <input type="number" id="calo" name="calo" class="form-control" required><br>

        <label for="quantity" class="form-label">Số lượng:</label>
        <input type="number" id="quantity" class="form-control" name="quantity" required><br>

        <label for="price" class="form-label">Giá:</label>
        <input type="number" id="price" class="form-control" name="price" step="1" required><br>
        
        <label for="discount" class="form-label">Giảm giá:</label><br>
        <input type="number" id="discount" name="discount" class="form-control" min="0" max="100"><br><br>

        <div id="categoriesContainer"></div>

        <label for="images" class="form-label">Ảnh:</label><br>
        <input type="file" id="images" class="form-control" name="images[]" multiple
            onchange="previewImages(event)"><br><br>

        <div id="uploadedImages" class="d-flex flex-wrap"></div>

        <input type="submit" value="Cập nhật sản phẩm" class="btn btn-primary">
    </form>
</div>


<script>
    const productId = {{ $product_id }}
    var productCategoryIds = [];
    document.getElementById('updateProductForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
        if (validateForm()) {
            const formData = new FormData(this);
            fetch(BASE_API_URL + 'products/' + productId, {
                method: 'POST', // Sử dụng phương thức POST
                headers: {
                    'X-HTTP-Method-Override': 'PUT', // Ghi đè phương thức POST bằng PUT
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    showToast('Thành công', 'Cập nhật sản phẩm thành công', 'bg-success');
                    console.log('Product updated successfully:', data);
                })
                .catch(error => {
                    showToast('Thất bại', 'Cập nhật sản phẩm thất bại', 'bg-danger');
                    console.error('Error updating product:', error);
                });
        }
    });

    fetch(BASE_API_URL + 'category/product/' + productId)
        .then(response => response.json())
        .then(categories => {
            const categoriesContainer = document.getElementById('categoriesContainer');
            categories.forEach(category => {
                productCategoryIds.push(category.id);
            });
        })
        .catch(error => console.error('Error:', error));

    fetch(BASE_API_URL + 'category')
        .then(response => response.json())
        .then(categories => {
            const categoriesContainer = document.getElementById('categoriesContainer');
            categories.forEach(category => {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'category[]';
                checkbox.value = category.id;
                if (productCategoryIds.includes(category.id)) {
                    checkbox.checked = true;
                }
                const label = document.createElement('label');
                label.htmlFor = 'category' + category.id;
                label.textContent = category.name;
                categoriesContainer.appendChild(checkbox);
                categoriesContainer.appendChild(label);
                categoriesContainer.appendChild(document.createElement('br'));
            });
        })
        .catch(error => console.error('Error fetching categories:', error));

    fetch(BASE_API_URL + 'product/' + productId)
        .then(response => response.json())
        .then(product => {
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('ingredient').value = product.ingredient;
            document.getElementById('calo').value = product.calo;
            document.getElementById('quantity').value = product.quantity;
            document.getElementById('price').value = product.price;
            document.getElementById('discount').value = product.discount;
            const uploadedImagesDiv = document.getElementById('uploadedImages');
            product.images.forEach(image => {
                const imgContainer = document.createElement('div');
                imgContainer.classList.add('imageContainer');

                const img = document.createElement('img');
                img.classList.add('oldImage');
                if (image.imgurl.startsWith('https')) {
                    img.src = image.imgurl; // Nếu URL bắt đầu bằng 'https', sử dụng URL trực tiếp
                } else {
                    img.src = "https://vanson.io.vn/food-api/storage/app/public/product_images/" + image.imgurl; // Nếu không, thêm đường dẫn cơ sở
                }
                img.style.maxWidth = '100px';
                const deleteIcon = document.createElement('span');
                deleteIcon.classList.add('deleteIcon', 'btn-close');
                deleteIcon.addEventListener('click', function () {
                    imgContainer.remove();
                });
                const oldImagesInput = document.createElement('input');
                oldImagesInput.type = 'hidden';
                oldImagesInput.id = 'oldImages[]';
                oldImagesInput.name = 'oldImages[]';
                oldImagesInput.value = image.imgurl;
                imgContainer.appendChild(img);
                imgContainer.appendChild(deleteIcon);
                imgContainer.appendChild(oldImagesInput);
                uploadedImagesDiv.appendChild(imgContainer);
            });
        })
        .catch(error => console.error('Error:', error));

    function previewImages(event) {
        const files = event.target.files;
        const uploadedImagesDiv = document.getElementById('uploadedImages');
        for (const file of files) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const imgContainer = document.createElement('div');
                imgContainer.classList.add('imageContainer');
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                const deleteIcon = document.createElement('span');
                deleteIcon.classList.add('deleteIcon', 'btn-close');
                deleteIcon.addEventListener('click', function () {
                    imgContainer.remove();
                });
                imgContainer.appendChild(img);
                imgContainer.appendChild(deleteIcon);
                uploadedImagesDiv.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        }
    }

    function validateForm() {
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();
        const ingredient = document.getElementById('ingredient').value.trim();
        const calo = document.getElementById('calo').value.trim();
        const quantity = document.getElementById('quantity').value.trim();
        const price = document.getElementById('price').value.trim();
        const discount = document.getElementById('discount').value.trim();

        if (!name) {
            showToast('Có lỗi', 'Tên sản phẩm không được để trống', 'bg-danger');
            return false;
        }
        if (!description) {
            showToast('Có lỗi', 'Mô tả không được để trống', 'bg-danger');
            return false;
        }
        if (!ingredient) {
            showToast('Có lỗi', 'Nguyên liệu không được để trống', 'bg-danger');
            return false;
        }
        if (!calo || !isPositiveNumber(calo)) {
            showToast('Có lỗi', 'Calo phải là số dương', 'bg-danger');
            return false;
        }
        if (!quantity || !isPositiveNumber(quantity)) {
            showToast('Có lỗi', 'Số lượng phải là số dương', 'bg-danger');
            return false;
        }
        if (!price || !isPositiveNumber(price)) {
            showToast('Có lỗi', 'Giá phải là số dương', 'bg-danger');
            return false;
        }
        if (discount && !isValidDiscount(discount)) {
            showToast('Có lỗi', 'Giảm giá phải từ 0 đến 100', 'bg-danger');
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

</script>
@endsection
