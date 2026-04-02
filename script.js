// ===================== GLOBAL DATA =====================
let donors = [];
let banks = [];

// ===================== LOAD DONORS =====================
function loadDonors() {
    fetch("get_donors.php")
        .then(res => res.json())
        .then(data => {
            donors = data;
            renderDonors(donors);
        })
        .catch(err => console.error("Error loading donors:", err));
}

// ===================== LOAD BLOOD BANKS =====================
function loadBanks() {
    fetch("get_banks.php")
        .then(res => res.json())
        .then(data => {
            banks = data;
            renderBanks();
        })
        .catch(err => console.error("Error loading banks:", err));
}

// ===================== NAVIGATION =====================
function showPage(page) {
    document.querySelectorAll(".page")
        .forEach(p => p.classList.remove("active"));

    document.getElementById("page-" + page).classList.add("active");

    document.querySelectorAll(".nav-links a")
        .forEach(a => a.classList.remove("active"));

    const nav = document.getElementById("nav-" + page);
    if (nav) nav.classList.add("active");

    window.scrollTo(0, 0);

    if (page === "find") loadDonors();
    if (page === "bloodbanks") loadBanks();
}



// ===================== RENDER DONORS =====================
function renderDonors(list) {
    const grid = document.getElementById("donorGrid");
    const noRes = document.getElementById("noResults");

    if (!grid) return;

    if (!list || list.length === 0) {
        grid.innerHTML = "";
        if (noRes) noRes.style.display = "block";
        return;
    }

    if (noRes) noRes.style.display = "none";

    grid.innerHTML = list.map(d => `
        <div class="donor-card">
            <div class="blood-badge">${d.blood_group || 'N/A'}</div>
            <div class="donor-info">
                <h3>${d.name || 'Anonymous'}</h3>
                <p>📍 ${d.city || 'Unknown'} | ${d.available == 1 ? "🟢 Available" : "🔴 Unavailable"}</p>
                <button class="btn-contact" onclick="contactDonor('${d.name}','${d.phone}')">
                    📞 Contact
                </button>
            </div>
        </div>
    `).join("");
}

// ===================== SEARCH DONORS =====================
function searchDonors() {
    const blood = document.getElementById("filterBlood").value;
    const city = document.getElementById("filterCity").value;

    const filtered = donors.filter(d =>
        (!blood || d.blood_group === blood) &&
        (!city || d.city === city)
    );

    renderDonors(filtered);
}

// ===================== CONTACT DONOR =====================
function contactDonor(name, phone) {
    alert(`📞 Contact ${name}\nPhone: ${phone}`);
}

// ===================== RENDER BLOOD BANKS =====================
function renderBanks() {
    const grid = document.getElementById("bankGrid");
    if (!grid) return;

    if (!banks || banks.length === 0) {
        grid.innerHTML = '<div class="no-results" style="display:block;"><span>🏥</span>No blood banks found.</div>';
        return;
    }

    grid.innerHTML = banks.map(b => {
        const types = b.blood_types ? b.blood_types.split(",") : [];
        return `
        <div class="bank-card">
            <h3>🏥 ${b.name || 'Unknown'}</h3>
            <div class="bank-addr">📍 ${b.address || 'Address not available'}</div>
            <div class="bank-meta">
                ${types.map(t => `<span class="bank-badge">${t.trim()}</span>`).join("")}
            </div>
            <div class="bank-phone">📞 ${b.phone || 'N/A'}</div>
            <div style="font-size:0.8rem;color:#777;">⏰ ${b.open_time || "Contact for timings"}</div>
        </div>
        `;
    }).join("");
}

// ===================== REGISTER FORM =====================
function switchTab(tab, event) {
    document.querySelectorAll(".tab-btn")
        .forEach(b => b.classList.remove("active"));
    
    if (event && event.target) {
        event.target.classList.add("active");
    }

    document.getElementById("donorForm").style.display = 
        tab === "donor" ? "block" : "none";
    document.getElementById("orgForm").style.display = 
        tab === "org" ? "block" : "none";
}

// ===================== SUBMIT DONOR FORM =====================
function submitForm(type) {
    if (type === "donor") {
        // Get all form values
        const firstName = document.querySelector("#donorForm input[placeholder='First Name']")?.value || '';
        const lastName = document.querySelector("#donorForm input[placeholder='Last Name']")?.value || '';
        const phone = document.querySelector("#donorForm input[placeholder='Phone Number']")?.value || '';
        const email = document.querySelector("#donorForm input[placeholder='Email Address']")?.value || '';
        const address = document.querySelector("#donorForm textarea")?.value || '';
        const age = document.querySelector("#donorForm input[placeholder='Age']")?.value || '';
        const bloodGroup = document.querySelector("#donorForm select")?.value || '';
        const district = document.querySelector("#donorForm select[placeholder='District']")?.value || 
                        document.querySelector("#donorForm select:nth-of-type(1)")?.value || '';
        const state = document.querySelector("#donorForm select[placeholder='State']")?.value ||
                    document.querySelector("#donorForm select:nth-of-type(2)")?.value || '';
        const pinCode = document.querySelector("#donorForm input[placeholder='Pin Code']")?.value || '';
        const lastDonationMonth = document.querySelector("#donorForm select[placeholder='Month']")?.value || '';
        const lastDonationYear = document.querySelector("#donorForm select#yearSelect")?.value || '';
        const consent = document.querySelector("#donorConsent")?.checked || false;

        // Validate required fields
        if (!firstName || !lastName || !phone || !bloodGroup || !district) {
            alert("Please fill all required fields (*) and accept the terms.");
            return;
        }

        if (!consent) {
            alert("Please accept the terms and conditions.");
            return;
        }

        // Create FormData
        const formData = new FormData();
        formData.append("first_name", firstName);
        formData.append("last_name", lastName);
        formData.append("phone", phone);
        formData.append("email", email);
        formData.append("address", address);
        formData.append("age", age);
        formData.append("blood_group", bloodGroup);
        formData.append("district", district);
        formData.append("state", state);
        formData.append("pin_code", pinCode);
        formData.append("last_donation_month", lastDonationMonth);
        formData.append("last_donation_year", lastDonationYear);

        fetch("add_donor.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())  // Changed from res.text() to res.json()
        .then(data => {
            if (data.success) {
                const successMsg = document.getElementById("donorSuccess");
                if (successMsg) {
                    // Show password in success message
                    successMsg.innerHTML = `🎉 ${data.message}<br><br>
                    <strong style="background: #e8f5e9; padding: 10px 15px; border-radius: 8px; display: inline-block; border: 1px solid #4caf50;">
                    🔐 Your Login Password: <span style="font-size: 1.2rem; letter-spacing: 1px;">${data.password}</span>
                    </strong><br><br>
                    <small>⚠️ Please save this password. You'll need it to login to your account.</small>`;
                    successMsg.style.display = "block";
                }
                // Reset form
                document.querySelector("#donorForm form")?.reset();
                setTimeout(() => {
                    if (successMsg) successMsg.style.display = "none";
                }, 15000); // Show for 15 seconds so user can note down password
                if (typeof loadDonors === 'function') loadDonors();
            } else {
                alert("Registration failed: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("An error occurred. Please try again.");
        });
    } 
    else if (type === "org") {
        const orgName = document.querySelector("#orgForm input[placeholder='Organization Name']")?.value || '';
        const address = document.querySelector("#orgForm textarea")?.value || '';
        const headName = document.querySelector("#orgForm input[placeholder='Full Name']")?.value || '';
        const phone = document.querySelector("#orgForm input[placeholder='Contact Number']")?.value || '';
        const email = document.querySelector("#orgForm input[placeholder='Organization Email']")?.value || '';
        const orgType = document.querySelector("#orgForm select")?.value || '';
        const consent = document.querySelector("#orgConsent")?.checked || false;

        if (!orgName || !address || !headName || !phone || !orgType) {
            alert("Please fill all required fields and accept the terms.");
            return;
        }

        if (!consent) {
            alert("Please accept the terms and conditions.");
            return;
        }

        const formData = new FormData();
        formData.append("org_name", orgName);
        formData.append("address", address);
        formData.append("head_name", headName);
        formData.append("phone", phone);
        formData.append("email", email);
        formData.append("org_type", orgType);

        fetch("add_organization.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const successMsg = document.getElementById("orgSuccess");
                if (successMsg) {
                    successMsg.innerHTML = `🎉 ${data.message}`;
                    successMsg.style.display = "block";
                }
                setTimeout(() => {
                    if (successMsg) successMsg.style.display = "none";
                }, 5000);
            } else {
                alert("Organization registration failed: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("An error occurred. Please try again.");
        });
    }
}
// ===================== CONTACT FORM =====================
function submitContact() {
    const form = document.getElementById('contactFormElement');
    if (!form) return;
    
    const name = form.querySelector("input[name='name']")?.value || '';
    const email = form.querySelector("input[name='email']")?.value || '';
    const phone = form.querySelector("input[name='phone']")?.value || '';
    const subject = form.querySelector("select[name='subject']")?.value || '';
    const message = form.querySelector("textarea[name='message']")?.value || '';

    if (!name || !email || !message) {
        alert("Please fill in your name, email, and message.");
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Sending...";
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append("name", name);
    formData.append("email", email);
    formData.append("phone", phone);
    formData.append("subject", subject);
    formData.append("message", message);

    fetch("contact.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        const successMsg = document.getElementById("contactSuccess");
        if (data.success) {
            if (successMsg) {
                successMsg.textContent = "✅ " + data.message;
                successMsg.style.display = "block";
            }
            form.reset();
            setTimeout(() => {
                if (successMsg) successMsg.style.display = "none";
            }, 5000);
        } else {
            alert(data.message || "Failed to send message. Please try again.");
        }
    })
    .catch(err => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        console.error("Error:", err);
        alert("An error occurred. Please try again.");
    });
}

// ===================== POPULATE YEAR SELECT =====================
function populateYearSelect() {
    const yearSelect = document.getElementById("yearSelect");
    if (yearSelect) {
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= 1950; i--) {
            const option = document.createElement("option");
            option.value = i;
            option.textContent = i;
            yearSelect.appendChild(option);
        }
    }
}

// ===================== INITIALIZE ON PAGE LOAD =====================
document.addEventListener("DOMContentLoaded", function() {
    populateYearSelect();
    showPage('home');
});