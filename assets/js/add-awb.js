(function ($) {
    $(document).ready(function () {
        // Save the original tb_show function
        const original_tb_show = window.tb_show;

        // Override tb_show to include a custom trigger
        window.tb_show = function (caption, url, imageGroup) {
            // Call the original tb_show function
            original_tb_show(caption, url, imageGroup);

            // After ThickBox is shown, execute your custom logic
            let addParcelButton = document.getElementById('addParcelButton');
            if (addParcelButton) {
                // Add the event listener only if the element exists
                addParcelButton.addEventListener('click', function () {
                    let modalContainer = document.getElementById("TB_window");
                    let packageDimensionInput = modalContainer.querySelector(".rowPackageDimension");

                    if (packageDimensionInput) {
                        let clonedPackageDimensionInput = packageDimensionInput.cloneNode(true);

                        // Update the name attribute with an incremented number
                        let allInputs = modalContainer.querySelectorAll(".rowPackageDimension input");
                        let lastIndex = allInputs.length;
                        clonedPackageDimensionInput.querySelectorAll("input").forEach((input) => {
                            let name = input.getAttribute("name");
                            if (name) {
                                let newName = name.replace(/\d+$/, '') + lastIndex;
                                input.setAttribute("name", newName);
                            }
                            input.value = ""; // Clear the value in the cloned input
                        });

                        packageDimensionInput.parentNode.insertBefore(clonedPackageDimensionInput, packageDimensionInput.nextSibling);
                        renumberInputs();
                        checkPackageLength();


                        document.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (input) {
                            input.addEventListener('change', function () {
                                let weight = 0;
                                document.querySelectorAll('.samedaycourier-package-weight-class').forEach(function (item) {
                                    weight += parseFloat(item.value) || 0;
                                });
                                let weightInput = document.getElementById("sameday-package-weight");
                                weightInput.value = "Calculated Weight: " + weight + " kg";
                            });
                        });
                    }
                });
            }
        };

        function checkPackageLength(){
            let packageWeightClass = document.querySelectorAll(".samedaycourier-package-weight-class");
            let packageLength = document.getElementById("samedaycourier-package-length");
            packageLength.value = packageWeightClass.length;
        }

        document.addEventListener('click', function (e) {
            if (document.querySelectorAll('.deleteParcelButton').length > 1) {
                if (e.target && e.target.classList.contains('deleteParcelButton')) { // Adjust the class as needed
                    let tableRow = e.target.closest('tr'); // Find the closest <tr> ancestor
                    if (tableRow) {
                        tableRow.remove(); // Remove the <tr>
                        renumberInputs();
                        checkPackageLength();
                    }
                }
            }
        });

        function renumberInputs() {
            // Renumber all inputs to ensure sequential naming
            let modalContainer = document.getElementById("TB_window");
            let allRows = modalContainer.querySelectorAll(".rowPackageDimension");
            allRows.forEach((row, index) => {
                row.querySelectorAll("input").forEach((input) => {
                    let name = input.getAttribute("name");
                    if (name) {
                        let newName = name.replace(/\d+$/, '') + (index + 1);
                        input.setAttribute("name", newName);
                    }
                });
            });
        }
    });
}(jQuery));
