<?php
class Database {
    private $host = "localhost";
    private $db_name = "apthub_db";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }

    // ✅ Tenant Registration
    public function registerTenant($firstname, $lastname, $username, $email, $phone, $password, $confirm_password) {
        if ($password !== $confirm_password) {
            return "Passwords do not match.";
        }

        $conn = $this->connect();
        $check = $conn->prepare("SELECT * FROM tenants WHERE username = :username OR email = :email");
        $check->bindParam(':username', $username);
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            return "Username or email already exists.";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO tenants (firstname, lastname, username, email, phone, password)
                                VALUES (:firstname, :lastname, :username, :email, :phone, :password)");
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute() ? true : "Registration failed.";
    }

    // ✅ Unified Login (Tenant or Admin)
    public function loginUser($username, $password, $role) {
    $conn = $this->connect();

    // Piliin ang tamang table depende sa role
    if ($role === "tenant") {
        $stmt = $conn->prepare("SELECT * FROM tenants WHERE username = :username OR email = :username LIMIT 1");
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username OR email = :username LIMIT 1");
    }

    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            session_start();

            if ($role === "tenant") {
                $_SESSION['user_id'] = $user['tenant_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['firstname'] . " " . $user['lastname'];
                $_SESSION['role'] = 'tenant';
                return "tenant";
            } else {
                $_SESSION['user_id'] = $user['admin_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = 'admin';
                return "admin";
            }
        } else {
            return "Incorrect password.";
        }
    } else {
        return "Account not found.";
    }
}


    // ✅ Logout
   public function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /ahub/index.php");
    exit();
}


    // ✅ Get Tenant Info
    public function getTenantInfo($tenant_id) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT * FROM tenants WHERE tenant_id = :tenant_id");
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Add Apartment ---
public function addApartment($name, $type, $location, $description, $rate, $imagePath) {
    $conn = $this->connect();
    $stmt = $conn->prepare("INSERT INTO apartments (Name, Type, Location, Description, MonthlyRate, Image)
                            VALUES (:name, :type, :location, :description, :rate, :image)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':rate', $rate);
    $stmt->bindParam(':image', $imagePath);
    return $stmt->execute();
}

// --- Get All Apartments (for index / tenant dashboard) ---
public function getAllApartments() {
    $conn = $this->connect();
    $stmt = $conn->query("SELECT * FROM apartments ORDER BY DateAdded DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Tenant Apply for Apartment ---
public function applyApartment($tenant_id, $apartment_id) {
    $conn = $this->connect();

    // check if already applied
    $check = $conn->prepare("SELECT * FROM applications WHERE tenant_id = :tenant AND apartment_id = :apartment");
    $check->bindParam(':tenant', $tenant_id);
    $check->bindParam(':apartment', $apartment_id);
    $check->execute();

    if ($check->rowCount() > 0) {
        return "You have already applied for this apartment.";
    }

    $stmt = $conn->prepare("INSERT INTO applications (tenant_id, apartment_id, status)
                            VALUES (:tenant, :apartment, 'Pending')");
    $stmt->bindParam(':tenant', $tenant_id);
    $stmt->bindParam(':apartment', $apartment_id);
    return $stmt->execute() ? true : "Failed to apply.";
}

// --- Admin Get All Applications ---
public function getAllApplications() {
    $conn = $this->connect();
    $stmt = $conn->query("
        SELECT a.application_id, t.firstname, t.lastname, ap.Name AS apartment_name,
               a.status, a.date_applied
        FROM applications a
        JOIN tenants t ON a.tenant_id = t.tenant_id
        JOIN apartments ap ON a.apartment_id = ap.ApartmentID
        ORDER BY a.date_applied DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Update Application Status (Approve / Reject) ---
public function updateApplicationStatus($application_id, $status) {
    $conn = $this->connect();
    $stmt = $conn->prepare("UPDATE applications SET status = :status WHERE application_id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $application_id);
    return $stmt->execute();
}
}
?>
