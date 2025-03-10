new Vue({
    el: '#app',
    data: {
        posts: [],
        newPost: { title: '', content: '' },
        user: { id: null, username: '' }
    },
    methods: {
        login() {
            // 这里可以添加登录逻辑，暂时手动设置用户
            this.user = { id: 1, username: 'testuser' };
        },
        register() {
            // 这里可以添加注册逻辑
        },
        createPost() {
            const postData = { ...this.newPost, user_id: this.user.id };
            console.log('发送的数据:', postData);
            axios.post('/livestock_forum/api/post.php', postData)
                .then(response => {
                    console.log('响应:', response);
                    if (response.data.status === 'success') {
                        this.loadPosts();
                        this.newPost = { title: '', content: '' };
                    }
                })
                .catch(error => {
                    console.error('请求失败:', error);
                    alert('发帖失败，请稍后重试');
                });
        },
        loadPosts() {
            axios.get('../api/get_posts.php')
                .then(response => {
                    this.posts = response.data;
                });
        }
    },
    mounted() {
        this.loadPosts();
    }
});
