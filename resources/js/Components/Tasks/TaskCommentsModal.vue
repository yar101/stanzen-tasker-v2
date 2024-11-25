<script>
import { useForm } from "@inertiajs/vue3";

export default {
    props: {
        task: {
            type: Object,
        },
    },

    data() {
        return {
            isCommentsModalOpen: false,
            commentsModalForm: useForm({
                task_id: null,
                created_by: null,
                content: '',
                comments: [],
            }),
        };
    },
};
</script>

<template>
    <div v-for="comment in task.comments" :key="comment.id">
        {{ comment.content }} . {{ comment.created_at }} . {{ comment.created_by }}
    </div>
</template>
