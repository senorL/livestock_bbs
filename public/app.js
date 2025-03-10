new Vue({
    el: '#app',
    data: {
        posts: [],
        newPost: { title: '', content: '' },
        user: { id: null, username: '' },
        showLoginForm: true,
        errorMessage: '',
        loginForm: {
            username: '',
            password: ''
        },
        registerForm: {
            username: '',
            email: '',
            password: ''
        }
    },
    methods: {
        toggleForm() {
            this.showLoginForm = !this.showLoginForm;
            this.errorMessage = '';
        },
        login() {
            // 验证表单
            if (!this.loginForm.username || !this.loginForm.password) {
                this.errorMessage = '请填写用户名和密码';
                return;
            }
            
            // 发送登录请求
            const formData = new FormData();
            formData.append('username', this.loginForm.username);
            formData.append('password', this.loginForm.password);
            
            axios.post('../api/login.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.user = {
                            id: response.data.user_id,
                            username: this.loginForm.username
                        };
                        this.errorMessage = '';
                        this.loginForm = { username: '', password: '' };
                        // 保存用户信息到本地存储
                        localStorage.setItem('user', JSON.stringify(this.user));
                    } else {
                        this.errorMessage = response.data.message || '登录失败，请检查用户名和密码';
                    }
                })
                .catch(error => {
                    console.error('登录请求失败:', error);
                    this.errorMessage = '登录失败，请稍后重试';
                });
        },
        register() {
            // 验证表单
            if (!this.registerForm.username || !this.registerForm.email || !this.registerForm.password) {
                this.errorMessage = '请填写所有必填字段';
                return;
            }
            
            // 简单的邮箱格式验证
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.registerForm.email)) {
                this.errorMessage = '请输入有效的电子邮箱地址';
                return;
            }
            
            // 发送注册请求
            const formData = new FormData();
            formData.append('username', this.registerForm.username);
            formData.append('email', this.registerForm.email);
            formData.append('password', this.registerForm.password);
            
            axios.post('../api/register.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        // 注册成功，切换到登录表单
                        this.showLoginForm = true;
                        this.errorMessage = '';
                        this.registerForm = { username: '', email: '', password: '' };
                        alert('注册成功，请登录');
                    } else {
                        this.errorMessage = response.data.message || '注册失败，请稍后重试';
                    }
                })
                .catch(error => {
                    console.error('注册请求失败:', error);
                    this.errorMessage = '注册失败，请稍后重试';
                });
        },
        logout() {
            this.user = { id: null, username: '' };
            localStorage.removeItem('user');
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
        // 检查本地存储中是否有用户信息
        const savedUser = localStorage.getItem('user');
        if (savedUser) {
            try {
                this.user = JSON.parse(savedUser);
            } catch (e) {
                console.error('解析用户信息失败:', e);
                localStorage.removeItem('user');
            }
        }
        this.loadPosts();
    }
});
