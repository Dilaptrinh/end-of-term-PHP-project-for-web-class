<?php
namespace App\Controllers;

use App\Core\View;
use App\Services\UserService;

/**
 * UserController
 * ------------------------------------------------------------------
 * Nhận request -> gọi Service -> chọn View.
 */
class UserController
{
    public function __construct(private UserService $service)
    {
    }

    // GET /users — danh sách
    public function index(): void
    {
        View::render('UserView', ['users' => $this->service->getAll()]);
    }

    // GET /users/create — form thêm (rỗng)
    public function create(): void
    {
        $this->renderForm('Thêm user', url('/users/create'), ['name' => '', 'age' => '']);
    }

    // POST /users/create — lưu user mới
    public function store(): void
    {
        $result = $this->service->create($_POST);
        if ($result['ok']) {
            redirect('/users');
        }
        // Có lỗi -> in lại form, giữ dữ liệu đã nhập
        $this->renderForm('Thêm user', url('/users/create'), $_POST, $result['errors']);
    }

    // GET /users/edit/{id} — form sửa
    public function edit(string $id): void
    {
        $user = $this->service->getById((int) $id);
        if (!$user) {
            http_response_code(404);
            die('Không tìm thấy user có id = ' . (int) $id);
        }

        $this->renderForm('Sửa user', url('/users/edit/' . $id), [
            'name' => $user->getName(),
            'age'  => $user->getAge(),
        ]);
    }

    // POST /users/edit/{id} — lưu thay đổi
    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $_POST);
        if ($result['ok']) {
            redirect('/users');
        }

        $this->renderForm('Sửa user', url('/users/edit/' . $id), $_POST, $result['errors']);
    }

    // POST /users/delete/{id} — xóa
    public function destroy(string $id): void
    {
        $this->service->delete((int) $id);
        redirect('/users');
    }

    // Hàm nội bộ: in form thêm/sửa
    private function renderForm(string $tieuDe, string $action, array $duLieu, array $errors = []): void
    {
        View::render('UserFormView', compact('tieuDe', 'action', 'duLieu', 'errors'));
    }
}
