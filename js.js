

var api = {
    get: function(url, type, data, success){
        console.log(url, type, data, success);
        $.ajax({
            'url': url,
            'type': type,
            'data': data,
            'success': function(data){
                if(data.success){
                    if(success) {
                        success(data.data);
                    }
                } else{
                    alert(data.error);
                }
            },
            'error': function(){
                alert('Error load. Try again');
            }
        })
    },
    like: function(post){
        var self = this;
        this.get(
            '/index.php?method=like',
            'POST',
            {post: post, user: getCookie('user')},
            function(){
                self.getPosts();
            }
        );
    },
    dislike: function(post){
        var self = this;
        this.get(
            '/index.php?method=dislike',
            'POST',
            {post: post, user: getCookie('user')},
            function(){
                self.getPosts();
            }
        );
    },
    getAllLikes: function(post){
        this.get(
            '/index.php?method=getLikes',
            'GET',
            {post: post},
            function(data){
                if(data.length){
                    var alert_mess = [];
                    for(var i in data){
                        alert_mess.push(data[i].username);
                    }
                    alert(alert_mess.join(', '));
                } else {
                    alert('Nobody');
                }
            }
        );
    },
    getPosts: function(){
        $('#posts').html('');
        this.get(
            '/index.php?method=index',
            'GET',
            {user: getCookie('user')},
            function(data){
                for(var i in data){
                    var post = '<div class="post">' + data[i].post + '';
                    if(data[i].like){
                        post += '<div title="Dislike" onclick="api.dislike(' + data[i].id + ')" class="dis like">-</div>';
                    } else {
                        post += '<div title="Like" onclick="api.like(' + data[i].id + ')" class="like">+</div>';
                    }
                    post += '<div onclick="api.getAllLikes(' + data[i].id + ')" class="all like" title="Get all likes">?</div></div>';

                    $('#posts').append(post)
                }
            }
        );
    },
    setUser: function(id, element){
        document.cookie='user=' + id;
        $('.users').removeClass('active');
        $(element).addClass('active');
        this.getPosts();
    },
    getUsers: function(){
        $('#list_of_user').html('');
        this.get(
            '/index.php?method=getUsers',
            'GET',
            '',
            function(data){
                for(var i in data){
                    $('#list_of_user').append(
                        '<div class="users ' + (getCookie('user') === data[i].id ? 'active' : '')  +  '" ' +
                        'onclick="api.setUser(' + data[i].id + ', this)">' + data[i].username + '</div>'
                    )
                }
            }
        );
    },
    addPost: function(){
        var self = this;
        this.get(
            '/index.php?method=addPost',
            'POST',
            {'post': $('#add_post').val()},
            function(){
                self.getPosts();
            }
        );
    },
    addUser: function(){
        var self = this;
        this.get(
            '/index.php?method=addUser',
            'POST',
            {'user': $('#add_user').val()},
            function(){
                self.getUsers();
            }
        );
    }
};

function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}