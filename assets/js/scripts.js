class Publik
{
    constructor()
    {
        this.lightbox = document.getElementById('publik-lightbox');
        this.postList = document.getElementById('post-list');
        this.buttons = {
            open: document.getElementById('publik-open-lightbox'),
            close: document.getElementById('publik-close-lightbox'),
            add: document.getElementById('publik-add-post'),
            save: document.getElementById('publik-save-button'),
            upload: document.getElementById('publish-lightbox-featured')
        }

        this.form = {};
        this.posts = [];

        this.loadedOptions = false;

        this.init();
    }

    init()
    {
        this.buttons.open.addEventListener( 'click', this.handleLightbox.bind(this) );
        this.buttons.close.addEventListener( 'click', this.handleLightbox.bind(this) );
        this.buttons.add.addEventListener( 'click', this.handleAddPost.bind(this) );
        this.buttons.save.addEventListener( 'click', this.handleSave.bind(this) );

        document.getElementById('publish-now').addEventListener( 'change', this.handleSchedule.bind(this) );

        this.buttons.upload.addEventListener( 'click', this.handleUploadFeatured.bind(this) );

        this.setPostList()
    }

    handleLightbox( event )
    {
        event.preventDefault();

        this.form = {
            title: document.getElementById('publish-title'),
            content: document.getElementById('publish-content_ifr'),
            author: document.getElementById('publish-author'),
            now: document.getElementById('publish-now'),
            schedule: document.getElementById('publish-schedule'),
            featured: document.getElementById('publish-featured'),
            categories: document.getElementById('publish-categories'),
            tags: document.getElementById('publish-tags'),
            facebook: document.getElementById('publish-facebook'),
            twitter: document.getElementById('publish-twitter'),
        }

        if ( !this.loadedOptions ) {
            this.loadSelectOptions();
            
            this.loadedOptions = true;
        }

        this.clearForm();

        let classes = this.lightbox.classList;
    
        if ( classes.contains('opened') ) {
            classes.remove('opened');
        } else {
            classes.add('opened');
        }    
    }

    handleUploadFeatured( event )
    {
        event.preventDefault();

        wp.media.editor.send.attachment = function(props, attachment) {
            document.getElementById('publish-featured').value = attachment.id;
        };

        wp.media.editor.open(this.buttons.upload);

        return false;
    }

    handleSchedule( event )
    {
        event.preventDefault();

        let schedule = document.getElementById('no-automatic');
        let classes = schedule.classList;

        if ( classes.contains('visible') ) {
            classes.remove('visible');
        } else {
            classes.add('visible');
        }
    }

    refactorData( form )
    {
        let values = Object.values(form).map(field => {

            let value = '';

            let id = field.getAttribute('id' );

            if ( id == 'publish-categories' || id == 'publish-tags' ) {

                let children = Object.values(field.selectedOptions).map(child => { 
                    return child.value;
                });

                value = children;

            } else if ( id == 'publish-content_ifr' ) {

                value = field.contentWindow.document.getElementsByTagName('body')[0].innerHTML;

            } else if ( id == 'publish-now' || id == 'publish-facebook' || id == 'publish-twitter' ) {

                value = field.checked === true ? true : false;

            } else {
                value = field.value;
            }

            return {
                id,
                value,
                field
            };
        });

        return values;
    }

    getPostObject( form )
    {
        let data = this.refactorData(form);
        let post = {};

        data.forEach( item => {
            let id = item.id.replace('publish-', '');

            if ( id === 'content_ifr' ) {
                id = 'content';
            }

            post[id] = item.value;
        });

        return post;
    }

    enableSaveButton()
    {
        if ( this.posts.length === 0 ) {
            this.buttons.save.setAttribute( 'disabled', true );
        } else {
            this.buttons.save.removeAttribute( 'disabled' );
        }        
    }

    hasError( post )
    {
        let error = false;

        if ( post.title === '' ) {
            this.form.title.parentNode.classList.add('field-error');
            error = true;
        } else {
            this.form.title.parentNode.classList.remove('field-error');
        }
        
        if ( post.content === '' || typeof post.content === 'undefined' ) {
            this.form.content.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.classList.add('field-error');
            error = true;
        } else {
            this.form.content.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.classList.remove('field-error');
        }

        return error;
    }

    handleAddPost( event )
    {
        event.preventDefault();

        let post = this.getPostObject(this.form);

        if ( this.hasError( post ) ) return;

        this.posts.push( post );

        this.setPostList();
        this.buttons.close.click();
    }

    handleSave( event )
    {
        event.preventDefault();

        if ( this.posts.length === 0 ) return;

        this.ajaxResquest();        
    }

    handleRemovePost( event )
    {
        let target = parseInt( event.target.parentNode.parentNode.getAttribute('data-target') );

        this.posts.splice( target, 1 );

        this.setPostList();
    }

    clearForm()
    {
        let form = this.form;

        form.title.value = '';
        form.content.contentWindow.document.getElementsByTagName('body')[0].innerHTML = '';
        form.now.value = 'on';
        form.schedule.value = '';
        form.featured.value = '';
        form.categories.value = '';
        form.tags.value = '';
        form.facebook.value = 'on';
        form.twitter.value = 'on';
    }

    getAuthorLabel( value )
    {
        let author = Object.values(this.form.author.children).filter(child => {
            return child.value == value;
        });

        return author[0].innerText;
    }

    addToPostList( post, index )
    {
        let liElement = document.createElement('li');
        liElement.setAttribute( 'data-target', index );

        let removeButton = document.createElement('button');
        removeButton.innerText = "✖";

        removeButton.addEventListener( 'click', this.handleRemovePost.bind(this) );

        let removeContainer = document.createElement('div');
        removeContainer.classList.add('remove');
        removeContainer.appendChild(removeButton);

       
        let date = 'Agora';
        if ( post.now !== true ) {
            date = new Intl.DateTimeFormat("pt-BR", {
                month: "numeric", day: "numeric", year: "2-digit", hour: "numeric", minute: "numeric"
            }).format( new Date( post.schedule ) );

            date = date.replace(' ', " - ");
        }

        liElement.innerHTML = `
            <div class="title">${post.title}</div>
            <div class="author">${this.getAuthorLabel(post.author)}</div>
            <div class="schedule">${date}</div>
        `;

        liElement.appendChild( removeContainer );
        this.postList.appendChild( liElement );
        
        this.clearForm();
    }

    setPostList()
    {
        this.enableSaveButton();

        let header = `
            <li class="label">
                <div class="title">Título</div>
                <div class="author">Autor</div>
                <div class="schedule">Hora/Data</div>
                <div class="remove">Ação</div>
            </li>
        `;

        let empty = `
            <li class="empty">
                <div class="title">
                    Nenhuma publicação agendada até o momento.
                </div>
            </li>
        `;

        this.postList.innerHTML = header;

        if ( this.posts.length === 0 ) { 
            this.postList.innerHTML += empty;

            return false;
        }

        this.posts.forEach((post, index) => {
            this.addToPostList( post, index );
        });
    }

    ajaxResquest()
    {
        let message = document.getElementById('publik-save-message');
        message.innerText = "Salvando dados...";

        let ajax = new Ajax();
        ajax.request( this.posts, 'publik_publish_response', ( response ) => {
            if ( response ) {
                message.innerText = "";
                
                this.posts = [];
                this.setPostList();
            }
        });
    }

    loadSelectOptions()
    {
        ajax_data.custom.authors.forEach(author => {
            let elem = document.createElement('option');
            elem.value = author.id;
            elem.innerText = author.display_name;

            this.form.author.append( elem );
        })

        ajax_data.custom.categories.forEach(author => {
            let elem = document.createElement('option');
            elem.value = author.id;
            elem.innerText = author.name;

            this.form.categories.append( elem );
        })

        ajax_data.custom.tags.forEach(author => {
            let elem = document.createElement('option');
            elem.value = author.id;
            elem.innerText = author.name;

            this.form.tags.append( elem );
        })
    }
}

let publik = new Publik();

class Ajax {
    request( data, action, callback ){
        let request_data = {
            'ajax_data_nonce': ajax_data.nonce,
            'action': action,
            'data': {...data}
        };
        
        jQuery.post( ajax_data.url, request_data, callback );
    }
}