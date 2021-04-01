<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contact as ContactModel;

class ContactUsController extends Controller
{

    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $messages = ContactModel::query();
        if (!empty($keyword)) {
            $messages = $messages->where('name', 'LIKE', '%' . $keyword . '%');
        }

        if (!empty($status)) {
            $messages = $messages->where('status', $status);
        }

        $messages = $messages->orderBy($sort_by, $order_by);
        $messages = $messages->paginate($limit_by);

        return view('backend.contact_us.index', compact('messages'));

    }

    public function show($id)
    {

        $message = ContactModel::findOrfail($id);
        if ($message && $message->status == 0) {
            $message->status = 1;
            $message->save();
        }
        return view('backend.contact_us.show', compact('message'));
    }

    public function destroy($id)
    {
        $message = ContactModel::findOrfail($id);

        if ($message) {
            $message->delete();

            return redirect()->route('admin.contact_us.index')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.contact_us.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }
}
