<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class BlogService
{
    /**
     * Get paginated list of blogs
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBlogs(array $filters = [])
    {
        $query = Blog::query();

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Search by title or description
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 15;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get a single blog by ID
     *
     * @param int $id
     * @return Blog
     * @throws ModelNotFoundException
     */
    public function getBlogById(int $id): Blog
    {
        $blog = Blog::find($id);

        if (!$blog) {
            throw new ModelNotFoundException('Blog not found');
        }

        return $blog;
    }

    /**
     * Create a new blog
     *
     * @param array $data
     * @return Blog
     */
    public function createBlog(array $data): Blog
    {
        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = fileUploader($data['image'], 'blogs');
        } elseif (isset($data['image']) && !is_string($data['image'])) {
            // If it's not a string and not an UploadedFile, remove it
            unset($data['image']);
        }

        // Handle schedule status
        $data = $this->handleScheduleStatus($data);

        // Set default is_active if not provided
        if (!isset($data['is_active'])) {
            $data['is_active'] = 0;
        }

        return Blog::create($data);
    }

    /**
     * Update an existing blog
     *
     * @param int $id
     * @param array $data
     * @return Blog
     * @throws ModelNotFoundException
     */
    public function updateBlog(int $id, array $data): Blog
    {
        $blog = Blog::find($id);

        if (!$blog) {
            throw new ModelNotFoundException('Blog not found');
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($blog->image) {
                deleteFile($blog->image);
            }
            $data['image'] = fileUploader($data['image'], 'blogs');
        } elseif (isset($data['image']) && !is_string($data['image'])) {
            // If it's not a string and not an UploadedFile, remove it
            unset($data['image']);
        }

        // Handle schedule status
        $data = $this->handleScheduleStatus($data, $blog);

        $blog->update($data);

        return $blog->fresh();
    }

    /**
     * Delete a blog
     *
     * @param int $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function deleteBlog(int $id): bool
    {
        $blog = Blog::find($id);

        if (!$blog) {
            throw new ModelNotFoundException('Blog not found');
        }

        // Delete associated image
        if ($blog->image) {
            deleteFile($blog->image);
        }

        return $blog->delete();
    }

    /**
     * Toggle blog active status
     *
     * @param int $id
     * @return Blog
     * @throws ModelNotFoundException
     */
    public function toggleActiveStatus(int $id): Blog
    {
        $blog = Blog::find($id);

        if (!$blog) {
            throw new ModelNotFoundException('Blog not found');
        }

        $blog->update([
            'is_active' => !$blog->is_active
        ]);

        return $blog->fresh();
    }

    /**
     * Get blog statistics
     *
     * @return array
     */
    public function getBlogStatistics(): array
    {
        return [
            'total' => Blog::count(),
            'published' => Blog::where('status', 'published')->count(),
            'draft' => Blog::where('status', 'draft')->count(),
            'scheduled' => Blog::where('status', 'scheduled')->count(),
            'active' => Blog::where('is_active', 1)->count(),
            'inactive' => Blog::where('is_active', 0)->count(),
        ];
    }

    /**
     * Handle schedule status logic
     *
     * @param array $data
     * @param Blog|null $blog
     * @return array
     */
    private function handleScheduleStatus(array $data, ?Blog $blog = null): array
    {
        $status = $data['status'] ?? ($blog ? $blog->status : 'draft');

        switch ($status) {
            case 'scheduled':
                // If status is scheduled, require publish_at
                if (empty($data['publish_at'])) {
                    throw new \InvalidArgumentException('publish_at is required when status is scheduled');
                }
                // Ensure publish_at is in the future
                $publishAt = Carbon::parse($data['publish_at']);
                if ($publishAt->isPast()) {
                    throw new \InvalidArgumentException('publish_at must be in the future for scheduled blogs');
                }
                $data['publish_at'] = $publishAt;
                break;

            case 'published':
                // If status is published and no publish_at, set to now
                if (empty($data['publish_at'])) {
                    $data['publish_at'] = Carbon::now();
                } else {
                    $data['publish_at'] = Carbon::parse($data['publish_at']);
                }
                break;

            case 'draft':
                // For draft, publish_at can be null
                if (isset($data['publish_at']) && !empty($data['publish_at'])) {
                    $data['publish_at'] = Carbon::parse($data['publish_at']);
                } else {
                    $data['publish_at'] = null;
                }
                break;
        }

        return $data;
    }
}
