new Vue({
    el: '#app',
    data: {
        post: null,
        comments: [],
        newComment: '',
        user: { id: null, username: '' },
        loading: true,
        postId: null
    },
    methods: {
        loadPost() {
            // 从URL获取帖子ID
            const urlParams = new URLSearchParams(window.location.search);
            this.postId = urlParams.get('id');
            
            if (!this.postId) {
                this.loading = false;
                return;
            }
            
            // 加载帖子详情
            axios.get(`../api/get_post.php?id=${this.postId}`)
                .then(response => {
                    this.post = response.data;
                    this.loading = false;
                    document.title = `${this.post.title} - 畜牧经验交流平台`;
                    this.loadComments();
                    this.loadLikes();
                    
                    // 确保作者名字可以链接到用户个人主页
                    if (this.post && this.post.author_id) {
                        this.$set(this.post, 'author_profile_url', `user_profile.html?id=${this.post.author_id}`);
                    }
                })
                .catch(error => {
                    console.error('加载帖子失败:', error);
                    this.loading = false;
                });
        },
        loadComments() {
            axios.get(`../api/get_comments.php?post_id=${this.postId}`)
                .then(response => {
                    this.comments = response.data;
                })
                .catch(error => {
                    console.error('加载评论失败:', error);
                });
        },
        addComment() {
            if (!this.newComment.trim()) {
                alert('评论内容不能为空');
                return;
            }
            
            const commentData = {
                content: this.newComment,
                user_id: this.user.id,
                post_id: this.postId
            };
            
            axios.post('../api/comment.php', commentData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.newComment = '';
                        this.loadComments();
                    } else {
                        alert(response.data.message || '评论失败，请稍后重试');
                    }
                })
                .catch(error => {
                    console.error('评论请求失败:', error);
                    alert('评论失败，请稍后重试');
                });
        },
        deletePost() {
            if (!confirm('确定要删除这篇帖子吗？此操作不可撤销。')) {
                return;
            }
            
            axios.post('../api/delete_post.php', {
                post_id: this.postId,
                user_id: this.user.id
            })
                .then(response => {
                    if (response.data.status === 'success') {
                        alert('帖子已成功删除');
                        window.location.href = 'index.html';
                    } else {
                        alert(response.data.message || '删除失败，请稍后重试');
                    }
                })
                .catch(error => {
                    console.error('删除请求失败:', error);
                    alert('删除失败，请稍后重试');
                });
        },
        loadLikes() {
            // 加载帖子的点赞信息
            if (!this.postId || !this.post) return;
            
            axios.get(`../api/get_likes.php?content_id=${this.postId}&content_type=post&user_id=${this.user.id || 0}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        // 更新帖子的点赞信息
                        this.$set(this.post, 'likes_count', response.data.likes_count);
                        this.$set(this.post, 'is_liked', response.data.is_liked);
                    }
                })
                .catch(error => {
                    console.error('加载点赞信息失败:', error);
                });
        },
        
        toggleLike() {
            if (!this.user.id) {
                alert('请先登录后再点赞');
                return;
            }
            
            const likeData = {
                user_id: this.user.id,
                content_id: this.postId,
                content_type: 'post'
            };
            
            axios.post('../api/like.php', likeData)
                .then(response => {
                    if (response.data.status === 'success') {
                        // 更新点赞状态
                        const action = response.data.action;
                        if (action === 'liked') {
                            this.post.is_liked = true;
                            this.post.likes_count = (this.post.likes_count || 0) + 1;
                        } else if (action === 'unliked') {
                            this.post.is_liked = false;
                            this.post.likes_count = Math.max(0, (this.post.likes_count || 0) - 1);
                        }
                    }
                })
                .catch(error => {
                    console.error('点赞操作失败:', error);
                    alert('点赞失败，请稍后重试');
                });
        },
        
        logout() {
            this.user = { id: null, username: '' };
            localStorage.removeItem('user');
            window.location.href = 'index.html';
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
        
        this.loadPost();
    }
});