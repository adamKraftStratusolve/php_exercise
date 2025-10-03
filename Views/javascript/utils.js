function handleFormSubmit(formId, apiEndpoint, options = {}) {
    const form = document.getElementById(formId);
    if (!form) return;

    const messageDiv = document.getElementById(options.messageId || 'message-display');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (messageDiv) messageDiv.style.display = 'none';

        if (options.beforeSubmit) {
            const proceed = options.beforeSubmit();
            if (!proceed) return;
        }

        const formData = new FormData(form);

        if (options.extraData) {
            for (const key in options.extraData) {
                formData.append(key, options.extraData[key]);
            }
        }

        apiService.post(apiEndpoint, formData)
            .then(data => {
                if (options.onSuccess) {
                    options.onSuccess(data);
                }
            })
            .catch(error => {
                if (messageDiv) {
                    messageDiv.textContent = error.message;
                    messageDiv.className = 'message error';
                    messageDiv.style.display = 'block';
                }
            });
    });
}

function createPostCard(post, options = {}) {
    const postCard = document.createElement('div');
    postCard.className = 'card post-card';
    postCard.setAttribute('data-post-id', post.PostID || post.PostId);

    const firstName = post.FirstName || '';
    const lastName = post.LastName || '';
    const username = post.Username || '';
    const avatarUrl = post.profile_image_url || '/Uploads/default-avatar.png';
    const avatarHTML = `<div class="post-avatar"><img src="${avatarUrl}" alt="${post.Username}'s avatar"></div>`;


    const headerHTML = options.showHeader ? `
        <div class="post-header">
            <div class="user-info">${firstName} ${lastName} <span>@${username}</span></div>
            <div class="timestamp">${new Date(post.CreatedAt).toLocaleString()}</div>
        </div>
    ` : '';

    const deleteButtonHTML = options.showDeleteButton ? `
        <button class="btn btn-danger delete-btn">Delete</button>
    ` : '';

    const postContentHTML = `
        <div class="post-content">
            <div class="post-header">
                <div class="user-info">${post.FirstName || ''} ${post.LastName || ''} <span>@${post.Username}</span></div>
            </div>
            <p class="post-body">${post.PostText}</p>
            <div class="post-meta">
                <span>${new Date(post.CreatedAt).toLocaleString()}</span>
                ${options.showDeleteButton ? '<button class="btn btn-danger delete-btn">Delete</button>' : ''}
            </div>
        </div>
    `;


    if (!options.showHeader) postCard.querySelector('.post-header')?.remove();
    if (!options.showDeleteButton && !postCard.querySelector('.post-meta span')) {
        postCard.querySelector('.post-meta')?.remove();
    }

    postCard.innerHTML = avatarHTML + postContentHTML;
    return postCard;
}