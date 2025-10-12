
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#4f46e5',
                        'primary-hover': '#4338ca',
                        'card-bg': '#26d320ff',
                        'bg-light': '#f3f4f6',
                        'text-dark': '#1f2937'
                    }
                }
            }
        }
        
        function submitForm(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Simple client-side validation check
            if (data.password.length < 8) {
                document.getElementById('message').textContent = 'Password must be at least 8 characters long.';
                document.getElementById('message').classList.remove('hidden', 'text-green-600');
                document.getElementById('message').classList.add('text-red-600');
                return;
            }

            // Display gathered data in the message box instead of submitting to a server
            const output = JSON.stringify(data, null, 2);
            document.getElementById('message').textContent = 'Registration Data Captured (Simulated):\n' + output;
            document.getElementById('message').classList.remove('hidden', 'text-red-600');
            document.getElementById('message').classList.add('text-green-600', 'whitespace-pre-wrap');

            // Clear the form fields after simulated submission
            form.reset();
        }
