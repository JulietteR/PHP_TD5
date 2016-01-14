<?php

use Gregwar\Image\Image;

$app->match('/', function() use ($app) {
    return $app['twig']->render('home.html.twig');
})->bind('home');

$app->match('/books', function() use ($app) {
    return $app['twig']->render('books.html.twig', array(
        'books' => $app['model']->getBooks()
        ));
})->bind('books');

$app->match('/aBook/{id}', function($id) use ($app) {
   $book = $app['model']->getABook($id)[0];
   $exemplaires =  $app['model']->getExemplairesABook($id);
   return $app['twig']->render('aBook.html.twig', array(
    'aBook' => $book,
    'exemplaires' => $exemplaires
    ));
})->bind('aBook');

$app->match('/loanABook/{id}', function($id) use ($app) {
    $request = $app['request'];
    $success = false;
    $exemplaires =  $app['model']->getExemplairesABook($id);
    if ($request->getMethod() == 'POST') {
       $post = $request->request;
       $endingDateWithTime = $post->get('end_date')."/".date('H/i/s');
      // get end time and add the hour/minutes/seconds when the book as been rented.
      
        // Saving the borrow to database
        $app['model']->borrowABook($post->get('borrower_name'), $id,  date('Y/m/d/H/i/s'), $endingDateWithTime   );
        $success = true;

    }
    return $app['twig']->render('loanABook.html.twig', array(
    'success' => $success
    ));

})->bind('loanABook');


$app->match('/returnABook/{id}', function($id) use ($app) {
      $app['model']->returnABook($id);

 
 return $app['twig']->render('home.html.twig'); /*, array(
    'success' => $success
    ));*/
 
})->bind('returnABook');




$app->match('/admin', function() use ($app) {
    $request = $app['request'];
    $success = false;
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('login') && $post->has('password') &&
            /*array($post->get('login'), $post->get('password')) == */
            $app['config']['admin'][$post->get('login')] == $post->get('password')) {
            $app['session']->set('admin', true);
        $success = true;
    }
}
return $app['twig']->render('admin.html.twig', array(
    'success' => $success
    ));
})->bind('admin');

$app->match('/logout', function() use ($app) {
    $app['session']->remove('admin');
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('logout');

$app->match('/addBook', function() use ($app) {
    if (!$app['session']->has('admin')) {
        return $app['twig']->render('shouldBeAdmin.html.twig');
    }

    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('title') && $post->has('author') && $post->has('synopsis') &&
            $post->has('copies')) {
            $files = $request->files;
        $image = '';

            // Resizing image
        if ($files->has('image') && $files->get('image')) {
            $image = sha1(mt_rand().time());
            Image::open($files->get('image')->getPathName())
            ->resize(240, 300)
            ->save('uploads/'.$image.'.jpg');
            Image::open($files->get('image')->getPathName())
            ->resize(120, 150)
            ->save('uploads/'.$image.'_small.jpg');
        }

            // Saving the book to database
        $app['model']->insertBook($post->get('title'), $post->get('author'), $post->get('synopsis'),
            $image, (int)$post->get('copies'));
    }
}

return $app['twig']->render('addBook.html.twig');
})->bind('addBook');

