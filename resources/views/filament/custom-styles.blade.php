<style>
    @font-face {
        font-family: 'Inter';
        src: url('/logo/font.otf') format('opentype');
        font-weight: normal;
        font-style: normal;
    }

    /* Sidebar Item Hover Override */
    .fi-sidebar-item>a:hover,
    .fi-sidebar-item>button:hover,
    .fi-sidebar-group>button:hover,
    .fi-sidebar-item-button:hover {
        background-color: #e8f7ef !important;
    }

    /* Optional: Change text color on hover if needed */
    /* .fi-sidebar-item > a:hover .fi-sidebar-item-label { color: #000 !important; } */

    /* Separator between sidebar groups */
    .fi-sidebar-group:not(:last-child) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
    }

    /* .fi-sidebar-item:not(:last-child) {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 4px;
        margin-bottom: 4px;
    } */
</style>

