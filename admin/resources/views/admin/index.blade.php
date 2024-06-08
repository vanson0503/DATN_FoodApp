@extends('../layout')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .btn-status,
    .btn-role,
    .btn-action {
        cursor: pointer;
        pointer-events: auto;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
        <h5 class="card-header">Danh sách tài khoản</h5>
        <div style="padding:10px">
            <button class="btn btn-action btn-info" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="fas fa-plus"></i> Thêm tài khoản
            </button>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Tình trạng</th>
                        <th>Vai trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="adminList">
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Thêm tài khoản quản trị</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAdminForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập:</label>
                        <input type="text" class="form-control" id="username_add" name="username_add" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu:</label>
                        <input type="password" class="form-control" id="password_add" name="password_add" required>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Địa chỉ ảnh:</label>
                        <input type="file" class="form-control" id="image_url" name="image_url" accept="image/*"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Tình trạng:</label>
                        <select class="form-select" id="status_add" name="status_add" required>
                            <option value="active">Hoạt động</option>
                            <option value="blocked">Khóa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò:</label>
                        <select class="form-select" id="role_add" name="role_add" required>
                            <option value="admin">Quản trị</option>
                            <option value="manager">Quản lý</option>
                            <option value="staff">Nhân viên</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="addAdmin()">Thêm</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="smallModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel2">Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="nameSmall" class="form-label">Bạn có chắc chắn muốn xóa?</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-danger" onClick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
<script>
    var currentDeletingAdminId = null;
    document.addEventListener("DOMContentLoaded", function () {
        fetchAdmins();
    });
    function deleteAdmin(adminId) {
        currentDeletingAdminId = adminId
    }
    function confirmDelete() {
        if (!currentDeletingAdminId) return;

        fetch(BASE_API_URL + `admin/${currentDeletingAdminId}`, {
            method: 'DELETE'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                showToast("Admin", "Xóa thành công!", "bg-success");
                currentDeletingAdminId = null
                fetchAdmins(); 
                $('#smallModal').modal('hide'); 
            })
            .catch(error => {
                showToast("Admin", "Xóa thất bại!", "bg-danger");
                $('#smallModal').modal('hide'); 
                console.error('There has been a problem with your fetch operation:', error);
            });
    }

    function fetchAdmins() {
        fetch(BASE_API_URL + 'admins')
            .then(response => response.json())
            .then(data => displayAdmins(data))
            .catch(error => console.error('Error:', error));
    }
    function addAdmin() {
        // Get form data
        const formData = new FormData(document.getElementById('addAdminForm'));

        // Get the username and validate
        const username = formData.get('username_add');
        const image = formData.get('image_url');
        
        if (!username || username.includes(' ')) {
            showToast("Lỗi", "Tên đăng nhập không được để trống và không có dấu cách!", "bg-warning");
            return;
        }
        
        if (!image ) {
            showToast("Lỗi", "Ảnh không được để trống!", "bg-warning");
            return;
        }

        // Check if all required fields are filled
        if (!formData.get('password_add') || !formData.get('image_url') || !formData.get('status_add') || !formData.get('role_add')) {
            showToast("Lỗi", "Vui lòng điền đầy đủ thông tin!", "bg-warning");
            return;
        }

        // Send form data to the API endpoint
        fetch(BASE_API_URL + 'admin/create', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                showToast("Admin", "Thêm thành công!", "bg-success")
                $('#addAdminModal').modal('hide');
                fetchAdmins()
            })
            .catch(error => {
                // Handle error
                showToast("Admin", "Thêm thất bại!", "bg-danger")
                $('#addAdminModal').modal('hide');
                console.error('Error adding admin:', error);
                // Optionally, you can display an error message to the user
            });
    }

    function displayAdmins(admins) {
        const adminList = document.getElementById('adminList');
        adminList.innerHTML = ""
        admins.forEach(admin => {
            const row = document.createElement('tr');
            const imageCell = document.createElement('td');
            const image = document.createElement('img');
            if(admin.image_url!=null){
                if (admin.image_url.startsWith('https')) {
                image.src = admin.image_url;
            } else {
                const baseUrl = 'https://vanson.io.vn/food-api/storage/app/public/avatars/';
                image.src = baseUrl + admin.image_url;
            }
            }
            image.style.width = '40px';
            image.style.height = 'auto';
            image.style.borderRadius = '50%';
            imageCell.appendChild(image);

            const nameCell = document.createElement('td');
            nameCell.textContent = admin.username;

            const statusCell = document.createElement('td');
            const statusBtn = document.createElement('button');
            statusBtn.className = `btn btn-status btn-${admin.status === 'active' ? 'success' : 'secondary'}`;
            statusBtn.textContent = capitalizeFirstLetter(admin.status);
            statusCell.appendChild(statusBtn);

            const roleCell = document.createElement('td');
            const roleBtn = document.createElement('button');
            roleBtn.className = `btn btn-role ${getRoleButtonClass(admin.role)}`;
            roleBtn.textContent = capitalizeFirstLetter(admin.role);
            roleCell.appendChild(roleBtn);

            const actionsCell = document.createElement('td');
            const editBtn = document.createElement('button');
            editBtn.setAttribute('title', 'Edit');
            editBtn.setAttribute('data-bs-toggle', 'modal');
            editBtn.setAttribute('data-bs-target', '#editAdminModal');
            editBtn.className = 'btn btn-action btn-warning';
            editBtn.innerHTML = '<i class="bx bx-edit-alt" ></i>'; // Font Awesome edit icon
            editBtn.onclick = function () {
                document.getElementById('username').value = admin.username;
                document.getElementById('id').value = admin.id;
                document.getElementById('status').value = admin.status;
                document.getElementById('role').value = admin.role;
            };
            actionsCell.appendChild(editBtn);

            const resetPasswordBtn = document.createElement('button');
            resetPasswordBtn.setAttribute('title', 'Reset password');
            resetPasswordBtn.setAttribute('data-bs-toggle', 'modal');
            resetPasswordBtn.setAttribute('data-bs-target', '#resetPasswordModal');
            resetPasswordBtn.className = 'btn btn-action btn-info';
            resetPasswordBtn.innerHTML = '<i class="bx bx-refresh" ></i>'; // Font Awesome reset icon
            resetPasswordBtn.onclick = function () {
                document.getElementById('id2').value = admin.id;
            };
            actionsCell.appendChild(resetPasswordBtn);


            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'btn btn-action btn-danger';
            deleteBtn.setAttribute('title', 'Delete');
            deleteBtn.setAttribute('data-bs-toggle', 'modal');
            deleteBtn.setAttribute('data-bs-target', '#smallModal');
            deleteBtn.innerHTML = '<i class="fas fa-trash" style="color: white;"></i>'; // Font Awesome trash icon
            deleteBtn.onclick = function () {
                deleteAdmin(admin.id);
            };
            actionsCell.appendChild(deleteBtn);


            row.appendChild(imageCell);
            row.appendChild(nameCell);
            row.appendChild(statusCell);
            row.appendChild(roleCell);
            row.appendChild(actionsCell);

            adminList.appendChild(row);
        });
    }

    function getRoleButtonClass(role) {
        switch (role.toLowerCase()) {
            case 'staff':
                return 'btn-info';
            case 'manager':
                return 'btn-primary';
            case 'admin':
                return 'btn-danger';
            default:
                return 'btn-secondary';
        }
    }

    function capitalizeFirstLetter(string) {
        if(string=="admin") return "Quản trị"
        if(string=="manager") return "Quản lý"
        if(string=="staff") return "Nhân viên"
        if(string=="active") return "Hoạt động"
        if(string=="blocked") return "Khóa"
        return string.charAt(0).toUpperCase() + string.slice(1);
    }


    function saveAdminChanges() {
        const id = document.getElementById('id').value
        // Tạo đối tượng JavaScript với dữ liệu từ form
        const adminData = {
            username: document.getElementById('username').value,
            status: document.getElementById('status').value,
            role: document.getElementById('role').value
        };

        // Gửi yêu cầu fetch đến API để tạo mới một admin
        fetch(BASE_API_URL + 'admin/update/' + id, {  // URL phụ thuộc vào thiết kế của API backend
            method: 'POST',  // Phương thức POST dùng để tạo mới dữ liệu
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',  // Đảm bảo API trả về dữ liệu dạng JSON
            },
            body: JSON.stringify(adminData)  // Chuyển đổi dữ liệu form thành chuỗi JSON
        })
            .then(response => {
                if (!response.ok) {  // Kiểm tra nếu có lỗi từ phía server, ví dụ như lỗi validation
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                fetchAdmins()
                showToast("Admin", "Sửa thành công!", "bg-success")
                $('#editAdminModal').modal('hide');  // Đóng modal sau khi cập nhật
                // Tải lại danh sách admin hoặc cập nhật giao diện người dùng tương ứng
            })
            .catch((error) => {
                showToast("Admin", "Sửa thành công!", "bg-danger")
                alert('Error creating admin.');  // Hiển thị thông báo lỗi
            });
    }

    function submitNewPassword() {
        const id = document.getElementById('id2').value;
        const password = document.getElementById('newPassword').value;
        const confirmNewPassword = document.getElementById('confirmNewPassword').value;

        // Đảm bảo mật khẩu mới được nhập đúng và xác nhận
        if (password == "" || confirmNewPassword == "") {
            showToast("Đổi mật khẩu", "Mật khẩu không được để trống!", "bg-danger")
            return;
        }
        if (password !== confirmNewPassword) {
            showToast("Đổi mật khẩu", "Mật khẩu không trùng nhau!", "bg-danger")
            return;
        }

        // Yêu cầu đến server để đặt lại mật khẩu
        // Thay thế URL_API với đường dẫn API đúng của bạn
        fetch(BASE_API_URL + 'admin/updatepassword/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ "password": password })
        })
            .then(response => response.json())
            .then(data => {
                showToast("Đổi mật khẩu", "Reset mật khẩu thành công!", "bg-success")
                $('#resetPasswordModal').modal('hide');
                document.getElementById('newPassword').value = "";
                document.getElementById('confirmNewPassword').value = "";
            })
            .catch(error => {
                showToast("Đổi mật khẩu", "Reset mật khẩu thất bại!", "bg-danger")
                //alert('Failed to reset password.');
            });
    }



</script>

<!-- Modal for Editing Admin -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAdminForm">
                    <input type="hidden" id="id" name="id" />
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Tình trạng</label>
                        <select class="form-select" id="status">
                            <option value="active">Hoạt động</option>
                            <option value="blocked">Khóa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Vai trò</label>
                        <select class="form-select" id="role">
                            <option value="staff">Nhân viên</option>
                            <option value="manager">Quản lý</option>
                            <option value="admin">Quản trị</option>
                        </select>
                    </div>
                    <!-- Additional fields can be added here -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="saveAdminChanges()">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Cấp lại mật khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm">
                    <input type="hidden" id="id2" name="id2" />
                    <div class="form-password-toggle">
                        <label class="form-label" for="newPassword">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="basic-default-password2" />
                            <span id="basic-default-password2" class="input-group-text cursor-pointer"><i
                                    class="bx bx-hide"></i></span>
                        </div>
                    </div>
                    <div class="form-password-toggle">
                        <label class="form-label" for="confirmNewPassword">Nhập lại mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmNewPassword"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="basic-default-password2" />
                            <span id="basic-default-password2" class="input-group-text cursor-pointer"><i
                                    class="bx bx-hide"></i></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="submitNewPassword()">Xác nhận</button>
            </div>
        </div>
    </div>
</div>


@endsection

