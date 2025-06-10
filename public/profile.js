new Vue({
    el: '#app',
    data: {
        user: null,
        currentUser: { id: null, username: '' },
        userPosts: [],
        followers: [],
        following: [],
        loading: true,
        showEditForm: false,
        showFollowers: false,
        showFollowing: false,
        isFollowing: false,
        editForm: {
            bio: '',
            gender: '',
            location: '',
        }
    },
    methods: {
        loadUser() {
            // 从URL获取用户ID
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('id');
            
            // 如果没有指定用户ID且当前用户已登录，则显示当前用户的个人主页
            if (!userId && this.currentUser.id) {
                this.loadUserData(this.currentUser.id);
                return;
            }
            
            // 如果指定了用户ID，则加载该用户的信息
            if (userId) {
                this.loadUserData(userId);
            } else {
                this.loading = false;
                this.user = null;
            }
        },
        
        loadUserData(userId) {
            axios.get(`../api/get_user.php?id=${userId}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.user = response.data.user;
                        document.title = `${this.user.username}的个人主页 - 畜牧经验交流平台`;
                        
                        // 初始化编辑表单数据
                        this.editForm = {
                            bio: this.user.bio || '',
                            gender: this.user.gender || '',
                            location: this.user.location || '',
                        };
                        
                        // 加载用户发布的帖子
                        this.loadUserPosts(userId);
                        
                        // 如果当前用户已登录，检查是否已关注该用户
                        if (this.currentUser.id && this.currentUser.id !== parseInt(userId)) {
                            this.checkFollowStatus(userId);
                        }
                    } else {
                        this.user = null;
                    }
                    this.loading = false;
                })
                .catch(error => {
                    console.error('加载用户信息失败:', error);
                    this.user = null;
                    this.loading = false;
                });
        },
        
        loadUserPosts(userId) {
            axios.get(`../api/get_user_posts.php?user_id=${userId}`)
                .then(response => {
                    this.userPosts = response.data;
                })
                .catch(error => {
                    console.error('加载用户帖子失败:', error);
                    this.userPosts = [];
                });
        },
        
        checkFollowStatus(userId) {
            axios.get(`../api/check_follow.php?follower_id=${this.currentUser.id}&following_id=${userId}`)
                .then(response => {
                    this.isFollowing = response.data.is_following;
                })
                .catch(error => {
                    console.error('检查关注状态失败:', error);
                    this.isFollowing = false;
                });
        },
        
        toggleFollow() {
            if (!this.currentUser.id) {
                alert('请先登录后再关注');
                return;
            }
            
            const followData = {
                follower_id: this.currentUser.id,
                following_id: this.user.id
            };
            
            axios.post('../api/toggle_follow.php', followData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.isFollowing = response.data.is_following;
                        
                        // 更新粉丝数
                        if (response.data.is_following) {
                            this.user.followers_count++;
                        } else {
                            this.user.followers_count = Math.max(0, this.user.followers_count - 1);
                        }
                    }
                })
                .catch(error => {
                    console.error('关注操作失败:', error);
                    alert('操作失败，请稍后重试');
                });
        },
        
        updateProfile() {
            if (!this.currentUser.id || this.currentUser.id !== this.user.id) {
                alert('您没有权限编辑此个人资料');
                return;
            }
            
            const profileData = {
                user_id: this.currentUser.id,
                ...this.editForm
            };
            
            axios.post('../api/update_profile.php', profileData)
                .then(response => {
                    if (response.data.status === 'success') {
                        // 更新用户信息
                        this.user = {
                            ...this.user,
                            bio: this.editForm.bio,
                            gender: this.editForm.gender,
                            location: this.editForm.location,
                        };
                        
                        this.showEditForm = false;
                        alert('个人资料已更新');
                    } else {
                        alert(response.data.message || '更新失败，请稍后重试');
                    }
                })
                .catch(error => {
                    console.error('更新个人资料失败:', error);
                    alert('更新失败，请稍后重试');
                });
        },
        
        loadFollowers() {
            if (!this.user || !this.user.id) return;
            
            axios.get(`../api/get_followers.php?user_id=${this.user.id}`)
                .then(response => {
                    this.followers = response.data;
                })
                .catch(error => {
                    console.error('加载粉丝列表失败:', error);
                    this.followers = [];
                });
        },
        
        loadFollowing() {
            if (!this.user || !this.user.id) return;
            
            axios.get(`../api/get_following.php?user_id=${this.user.id}`)
                .then(response => {
                    this.following = response.data;
                })
                .catch(error => {
                    console.error('加载关注列表失败:', error);
                    this.following = [];
                });
        },
        
        logout() {
            this.currentUser = { id: null, username: '' };
            localStorage.removeItem('user');
            window.location.href = 'index.html';
        }
    },
    watch: {
        showFollowers(val) {
            if (val) {
                this.loadFollowers();
            }
        },
        showFollowing(val) {
            if (val) {
                this.loadFollowing();
            }
        }
    },
    mounted() {
        // 检查本地存储中是否有用户信息
        const savedUser = localStorage.getItem('user');
        if (savedUser) {
            try {
                this.currentUser = JSON.parse(savedUser);
            } catch (e) {
                console.error('解析用户信息失败:', e);
                localStorage.removeItem('user');
            }
        }
        
        this.loadUser();
    }
});