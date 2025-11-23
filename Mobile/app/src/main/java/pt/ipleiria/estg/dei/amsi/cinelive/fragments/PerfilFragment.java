package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.EditarPerfilActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentPerfilBinding;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link PerfilFragment#newInstance} factory method to
 * create an instance of this fragment.
 */
public class PerfilFragment extends Fragment {
    private FragmentPerfilBinding binding;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentPerfilBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

//    @Override
//    public void onResume() {
//        super.onResume();
//        if (!AuthManager.isLoggedIn(getContext())) {
//            startActivity(new Intent(getActivity(), LoginActivity.class));
//            requireActivity().finish();
//        }
//    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
        inflater.inflate(R.menu.menu_configuracoes, menu);
        super.onCreateOptionsMenu(menu, inflater);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        if (item.getItemId() == R.id.itemConfiguracoes) {
            startActivity(new Intent(getActivity(), ConfiguracoesActivity.class));
            return true;
        }

        return super.onOptionsItemSelected(item);
    }


    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {

        // TODO: CHANGE THIS
        binding.etUsername.setText("john.smith");
        binding.etNome.setText("John Smith");
        binding.etEmail.setText("john.smith@email.com");
        binding.etTelemovel.setText("912345678");

        binding.btnEditarPerfil.setOnClickListener(v -> {
            startActivity(new Intent(getActivity(), EditarPerfilActivity.class));
        });

        binding.btnEliminarConta.setOnClickListener(v -> {
            new com.google.android.material.dialog.MaterialAlertDialogBuilder(v.getContext())
                    .setTitle(R.string.btn_eliminar_conta)
                    .setMessage(R.string.message_eliminar_conta)
                    .setPositiveButton(R.string.btn_eliminar_conta, (dialog, which) -> {

                        // TODO: chamar API para apagar conta

                    })
                    .setNegativeButton(R.string.btn_cancelar, null)
                    .show();
        });

    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}