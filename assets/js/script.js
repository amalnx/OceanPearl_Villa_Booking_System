```javascript
document.addEventListener("DOMContentLoaded", function () {

    const checkIn = document.querySelector('input[name="check_in"]');
    const checkOut = document.querySelector('input[name="check_out"]');

    if (checkIn && checkOut) {

        // Update minimum checkout date when check-in changes
        checkIn.addEventListener("change", function () {

            if (checkIn.value) {

                checkOut.min = checkIn.value;

                if (checkOut.value && checkOut.value <= checkIn.value) {
                    checkOut.value = "";
                }

            }

        });

        // Validate checkout date
        checkOut.addEventListener("change", function () {

            if (checkIn.value && checkOut.value <= checkIn.value) {

                alert("Check-out date must be after the check-in date.");

                checkOut.value = "";

            }

        });

    }

});
```