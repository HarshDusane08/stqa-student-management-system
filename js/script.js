function validateStudentForm() {
    const studentId = document.getElementById("student_id").value.trim();
    const fullName = document.getElementById("full_name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const totalClasses = parseInt(document.getElementById("total_classes").value.trim(), 10);
    const attendedClasses = parseInt(document.getElementById("attended_classes").value.trim(), 10);

    if (studentId.length < 3) {
        alert("Validation Error: Student ID must have at least 3 characters.");
        return false;
    }

    if (fullName.length < 3) {
        alert("Validation Error: Full Name must have at least 3 characters.");
        return false;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Validation Error: Please enter a valid Email address.");
        return false;
    }

    const phonePattern = /^[0-9]{10}$/;
    if (!phonePattern.test(phone)) {
        alert("Validation Error: Phone Number must be exactly 10 digits.");
        return false;
    }

    // Attendance Business Logic Check
    if (isNaN(totalClasses) || isNaN(attendedClasses) || totalClasses < 0 || attendedClasses < 0) {
        alert("Validation Error: Class counts cannot be negative numbers.");
        return false;
    }

    if (attendedClasses > totalClasses) {
        alert("Business Logic Error: Attended classes cannot exceed total classes conducted.");
        return false;
    }

    return true;
}

function confirmDelete(studentId) {
    return confirm("Are you sure you want to delete student: " + studentId + "?");
}