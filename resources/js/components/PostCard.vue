<template>
  <div class="bg-white rounded-lg shadow-md">
    <!-- Post Header -->
    <div class="p-4 flex items-center">
      <img 
        :src="`https://ui-avatars.com/api/?name=${post.user.name}`" 
        class="h-10 w-10 rounded-full"
        :alt="post.user.name"
      />
      <div class="ml-3">
        <p class="font-semibold">{{ post.user.name }}</p>
        <p class="text-gray-500 text-sm">{{ formatDate(post.created_at) }}</p>
      </div>
    </div>

    <!-- Post Image -->
    <img 
      :src="post.image_url" 
      class="w-full h-auto"
      :alt="post.caption"
    />

    <!-- Post Actions -->
    <div class="p-4 flex items-center space-x-4">
      <button 
        @click="toggleLike"
        :class="{ 'text-red-500': isLiked, 'text-gray-500': !isLiked }"
        class="flex items-center space-x-1"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <span>{{ likesCount }} likes</span>
      </button>

      <button class="flex items-center space-x-1 text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <span>{{ commentsCount }} comments</span>
      </button>
    </div>

    <!-- Post Caption -->
    <div class="px-4 pb-2">
      <p><span class="font-semibold">{{ post.user.name }}</span> {{ post.caption }}</p>
    </div>

    <!-- Comments -->
    <div class="px-4 pb-4">
      <div v-for="comment in post.comments" :key="comment.id" class="mt-2">
        <p>
          <span class="font-semibold">{{ comment.user.name }}</span>
          {{ comment.content }}
        </p>
      </div>

      <!-- Add Comment -->
      <form @submit.prevent="addComment" class="mt-4 flex">
        <input
          v-model="newComment"
          type="text"
          placeholder="Add a comment..."
          class="flex-1 border rounded-l px-4 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded-r hover:bg-blue-600"
        >
          Post
        </button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    post: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      newComment: '',
      likesCount: 0,
      commentsCount: 0,
      isLiked: false
    }
  },

  mounted() {
    this.likesCount = this.post.likes?.length || 0;
    this.commentsCount = this.post.comments?.length || 0;
    this.isLiked = this.post.likes?.some(like => like.user_id === this.$page.props.auth.user.id) || false;
  },

  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },

    async toggleLike() {
      try {
        await axios.post(`/posts/${this.post.id}/like`);
        this.isLiked = !this.isLiked;
        this.likesCount += this.isLiked ? 1 : -1;
      } catch (error) {
        console.error('Error toggling like:', error);
      }
    },

    async addComment() {
      if (!this.newComment.trim()) return;

      try {
        await axios.post(`/posts/${this.post.id}/comment`, {
          content: this.newComment
        });
        this.commentsCount += 1;
        this.newComment = '';
        this.$emit('comment-added');
      } catch (error) {
        console.error('Error adding comment:', error);
      }
    }
  }
}
</script>
