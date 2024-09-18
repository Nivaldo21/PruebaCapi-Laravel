<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Phone;
use App\Models\Email;
use App\Models\Address;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::paginate(20);
        return response()->json($contacts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'birthday' => 'required|date',
            'website' => 'nullable|url',
            'company' => 'nullable|string|max:255',
            'phones' => 'array',
            'emails' => 'array',
            'addresses' => 'array',
        ]);

        $contact = Contact::create($validated);

        // Save phones
        if ($request->phones) {
            foreach ($request->phones as $phone) {
                Phone::create([
                    'contact_id' => $contact->id,
                    'number' => $phone['phone']
                ]);
            }
        }

        // Save emails
        if ($request->emails) {
            foreach ($request->emails as $email) {
                Email::create([
                    'contact_id' => $contact->id,
                    'email' => $email['email']
                ]);
            }
        }

        // Save addresses
        if ($request->addresses) {
            foreach ($request->addresses as $address) {
                Address::create([
                    'contact_id' => $contact->id,
                    'street' => $address['street'],
                    'city' => $address['city'],
                    'state' => $address['state'],
                    'zip' => $address['zip'],
                    'country' => $address['country']
                ]);
            }
        }

        return response()->json($contact->load(['phones', 'emails', 'addresses']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::with(['phones', 'emails', 'addresses'])->findOrFail($id);
        return response()->json($contact);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'birthday' => 'required|date',
            'website' => 'nullable|url',
            'company' => 'nullable|string|max:255',
            'phones' => 'array',
            'emails' => 'array',
            'addresses' => 'array',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update($validated);

        // Update Phones
        Phone::where('contact_id', $contact->id)->delete();
        foreach ($request->phones as $phone) {
            Phone::create([
                'contact_id' => $contact->id,
                'number' => $phone['number']
            ]);
        }

        // Update emails
        Email::where('contact_id', $contact->id)->delete();
        foreach ($request->emails as $email) {
            Email::create([
                'contact_id' => $contact->id,
                'email' => $email['email']
            ]);
        }

        // Updates addresses
        Address::where('contact_id', $contact->id)->delete();
        foreach ($request->addresses as $address) {
            Address::create([
                'contact_id' => $contact->id,
                'street' => $address['street'],
                'city' => $address['city'],
                'state' => $address['state'],
                'zip' => $address['zip'],
                'country' => $address['country']
            ]);
        }

        return response()->json($contact->load(['phones', 'emails', 'addresses']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }

    /**
     * Search for the specific contact by different specific data
     */
    public function search(Request $request)
    {
        // Get the request search term
        $searchTerm = $request->input('query');

        // Validate that the search term is not empty.
        if (!$searchTerm) {
            return response()->json(['message' => 'You must provide a search term.'], 400);
        }

        // Search multiple Contact fields and associated relationships
        $contacts = Contact::where('name', 'like', "%{$searchTerm}%")
            ->orWhere('notes', 'like', "%{$searchTerm}%")
            ->orWhere('company', 'like', "%{$searchTerm}%")
            ->orWhereHas('phones', function ($query) use ($searchTerm) {
                $query->where('number', 'like', "%{$searchTerm}%");
            })
            ->orWhereHas('emails', function ($query) use ($searchTerm) {
                $query->where('email', 'like', "%{$searchTerm}%");
            })
            ->orWhereHas('addresses', function ($query) use ($searchTerm) {
                $query->where('street', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('state', 'like', "%{$searchTerm}%")
                    ->orWhere('zip', 'like', "%{$searchTerm}%")
                    ->orWhere('country', 'like', "%{$searchTerm}%");
            })
            ->with(['phones', 'emails', 'addresses'])
            ->paginate(20);

        // Check if no results were found
        if ($contacts->isEmpty()) {
            return response()->json(['message' => 'No contacts found.'], 404);
        }

        return response()->json($contacts);
    }
}
