module.exports = function(grunt) {

// Project configuration.
grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    concat: {
        basic: {
            src:[
                    'dist/header.js', 
                    'dist/model.js', 
                    'dist/view.js', 
                    'dist/router.js', 

                    // Standalone Scripts.
                    'dist/tasks/tasks-add.js',
                    'dist/tasks/tasks-edit.js',
                    'dist/tasks/tasks-delete.js',

                    'dist/comments/comment-add.js',
                    'dist/comments/comment-delete.js',

                    'dist/projects/project-edit.js',
                    'dist/projects/project-delete.js',

                    'dist/footer.js',
                ],
            dest: "thrive.dev.js"
        }
    },
    uglify: {
        my_target: {
            files: {
                "thrive.min.js": ["thrive.dev.js"]
            }
        },
        options: {
            compress: true,
            mangle: true,
            sourceMap: true,
            sourceMapIncludeSources: true,
        }
    },
    watch: {
        scripts: {
            files: [
                'dist/header.js', 
                'dist/model.js', 
                'dist/view.js', 
                'dist/router.js', 

                // Standalone Scripts.
                'dist/tasks/tasks-add.js',
                'dist/tasks/tasks-edit.js',
                'dist/tasks/tasks-delete.js',

                'dist/comments/comment-add.js',
                'dist/comments/comment-delete.js',

                'dist/projects/project-edit.js',
                'dist/projects/project-delete.js',

                'dist/footer.js',
            ],
            tasks: ['concat', 'uglify'],
            options: {
                interrupt: true,
            },
        },
    },

    jsbeautifier: {
        files : ["dist/*.js", "dist/comments/*.js", "dist/projects/*.js", "dist/tasks/*.js"],
        options: {
            js: {
                jslintHappy: false,
                indentSize: 4
            }
        }
    },

    jshint: {
        all: ['thrive.dev.js']
    }

});

// Load the plugin that provides the "uglify" task.
grunt.loadNpmTasks('grunt-contrib-uglify');

// Load the plugin that provides the "watch" task.
grunt.loadNpmTasks('grunt-contrib-watch');

// Load the plugin that provides the "concat" task.
grunt.loadNpmTasks('grunt-contrib-concat');

// Load the plugin that provides the "jsbeautifier" task.
grunt.loadNpmTasks('grunt-jsbeautifier');

// Load 'JSHint' plugin
grunt.loadNpmTasks('grunt-contrib-jshint');

// Default task(s).
grunt.registerTask('default', ['watch']);

};