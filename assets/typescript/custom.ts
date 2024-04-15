// console.log('Hello from assets/typescript/custom.ts 🎉');

// TODO: Temporarily - Check in SonataUserBundle for the user create button template
document.addEventListener('DOMContentLoaded', function() {
    let userCreateButton 
        = document.querySelector('a.sonata-action-element[href="/admin/app/user/create"]') as HTMLAnchorElement;

    if (userCreateButton) {
        userCreateButton.classList.remove('sonata-action-element');
        userCreateButton.classList.add('btn', 'btn-success');
        userCreateButton.style.padding = '6px 12px';
        userCreateButton.style.color = '#fff';
        userCreateButton.innerHTML = '&nbsp;&nbsp;Add New';

        let icon = document.createElement('i');
        icon.className = 'fas fa-plus-circle';
        icon.setAttribute('aria-hidden', 'true');
        userCreateButton.prepend(icon);

        userCreateButton.addEventListener('mouseover', function() {
            userCreateButton.style.backgroundColor = '#008d4c'; // Change to the color you want on hover
        });

        userCreateButton.addEventListener('mouseout', function() {
            userCreateButton.style.backgroundColor = ''; // Change back to the original color
        });
    }
});