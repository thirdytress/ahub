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
// --- Tenant Apply for Apartment ---
public function applyApartment($tenant_id, $apartment_id) {
    $conn = $this->connect();

    // 1️⃣ Check if apartment exists and is available
    $stmt = $conn->prepare("SELECT Status FROM apartments WHERE ApartmentID = :apt_id LIMIT 1");
    $stmt->bindParam(':apt_id', $apartment_id);
    $stmt->execute();
    $apt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$apt) {
        return "Apartment not found.";
    }

    if ($apt['Status'] !== 'Available') {
        return "This apartment is not available.";
    }

    // 2️⃣ Check if tenant already applied or already approved
    $stmt = $conn->prepare("
        SELECT * FROM applications 
        WHERE tenant_id = :tenant_id AND apartment_id = :apt_id AND status IN ('Pending','Approved')
    ");
    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':apt_id', $apartment_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return "You have already applied for this apartment.";
    }

    // 3️⃣ Insert new application
    $stmt = $conn->prepare("
        INSERT INTO applications (tenant_id, apartment_id, status, date_applied)
        VALUES (:tenant_id, :apt_id, 'Pending', NOW())
    ");
    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':apt_id', $apartment_id);

    return $stmt->execute() ? true : "Failed to submit application.";
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

public function getAvailableApartments($tenant_id) {
    $conn = $this->connect();

    $stmt = $conn->prepare("
        SELECT * FROM apartments 
        WHERE Status = 'Available'
        AND ApartmentID NOT IN (
            SELECT apartment_id FROM applications 
            WHERE tenant_id = :tenant_id AND status IN ('Pending','Approved')
        )
        ORDER BY DateAdded DESC
    ");
    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getTenantLeases($tenant_id) {
    $conn = $this->connect();

    $stmt = $conn->prepare("
        SELECT l.lease_id, l.start_date, l.end_date, 
               a.Name AS apartment_name, a.Location, a.MonthlyRate
        FROM leases l
        JOIN apartments a ON l.apartment_id = a.ApartmentID
        WHERE l.tenant_id = :tenant_id
        ORDER BY l.start_date DESC
    ");
    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- Add Lease when application is approved
// --- Add Lease for Tenant ---
public function createLease($tenant_id, $apartment_id, $start_date = null, $end_date = null) {
    $conn = $this->connect();

    // Default: lease starts today and lasts 1 year if not provided
    if (!$start_date) $start_date = date('Y-m-d');
    if (!$end_date) $end_date = date('Y-m-d', strtotime('+1 year'));

    $stmt = $conn->prepare("
        INSERT INTO leases (tenant_id, apartment_id, start_date, end_date)
        VALUES (:tenant_id, :apartment_id, :start_date, :end_date)
    ");
    $stmt->bindParam(':tenant_id', $tenant_id);
    $stmt->bindParam(':apartment_id', $apartment_id);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);

    return $stmt->execute();
}




}
?>
