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
import pt.ipleiria.estg.dei.amsi.cinelive.activities.MainActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentPerfilBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PerfilManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Perfil;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

public class PerfilFragment extends Fragment {
    private FragmentPerfilBinding binding;
    private AuthManager authManager;
    private PerfilManager perfilManager;


    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);

        // Obter o auth manager
        authManager = AuthManager.getInstance();

        // Obter o perfil manager
        perfilManager = PerfilManager.getInstance();
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentPerfilBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
        if (ConnectionUtils.hasInternet(requireContext())) {
            inflater.inflate(R.menu.menu_configuracoes, menu);
            super.onCreateOptionsMenu(menu, inflater);
        }
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

        // Esconder campo de password
        binding.form.tilPassword.setVisibility(View.GONE);

        if (!ConnectionUtils.hasInternet(requireContext())) {
            binding.btnEditarPerfil.setVisibility(View.GONE);
            binding.btnEliminarConta.setVisibility(View.GONE);
        }

        Perfil perfil = perfilManager.getPerfil();

        if (perfil != null) {
            binding.form.etUsername.setText(perfil.getUsername());
            binding.form.etEmail.setText(perfil.getEmail());
            binding.form.etNome.setText(perfil.getNome());
            binding.form.etTelemovel.setText(perfil.getTelemovel());
        }

        binding.btnEditarPerfil.setOnClickListener(v -> {
            Intent intent = new Intent(getActivity(), EditarPerfilActivity.class);
            startActivity(intent);
        });

        binding.btnEliminarConta.setOnClickListener(v -> {
            new com.google.android.material.dialog.MaterialAlertDialogBuilder(v.getContext())
                .setTitle(R.string.btn_eliminar_conta)
                .setMessage(R.string.msg_eliminar_conta)
                .setPositiveButton(R.string.btn_eliminar_conta, (dialog, which) -> {
                    //AuthManager.deleteAccount();
                }).setNegativeButton(R.string.btn_cancelar, null).show();
        });

        binding.btnLogout.setOnClickListener(v -> {
            // Logout
            authManager.logout(requireContext());

            // Reiniciar a MainActivity
            Intent intent = new Intent(requireContext(), MainActivity.class);
            intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(intent);

            requireActivity().finish();
        });
    }

    public void loadPerfil() {

    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}