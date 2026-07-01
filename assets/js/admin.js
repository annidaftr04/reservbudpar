function confirmAdminLogout(event) {
  event.preventDefault();
  Swal.fire({
    title: 'Logout Admin?',
    text: 'Anda yakin ingin keluar dari dashboard admin?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Logout',
    cancelButtonText: 'Batal',
    borderRadius: '20px',
  }).then(result => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Sedang logout...',
        text: 'Mohon tunggu sebentar',
        icon: 'success',
        timer: 1200,
        showConfirmButton: false,
        allowOutsideClick: false,
      });
      setTimeout(() => {
        window.location.href = 'logout_admin.php';
      }, 1200);
    }
  });
}
