(function () {
            const list = document.getElementById("subjects-list");
            const addButton = document.getElementById("add-subject");
            let index = list.children.length;

            function addSubjectForm() {
                const prototype = list.getAttribute("data-prototype");
                let newForm = prototype.replace(/__name__/g, index);

                // create container
                const li = document.createElement("li");
                li.className = "mb-3 border rounded p-3";

                li.innerHTML =
                    newForm +
                    '\n<div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger remove-subject">Remove</button></div>';

                Array.from(li.querySelectorAll("input, select, textarea")).forEach(
                    function (el) {
                        el.classList.add("form-control");
                    }
                );

                list.appendChild(li);
                index++;
            }

            function removeSubject(e) {
                if (!e.target.classList.contains("remove-subject")) return;
                const li = e.target.closest("li");
                if (li) li.remove();
            }

            addButton.addEventListener("click", addSubjectForm);
            list.addEventListener("click", removeSubject);
        })();